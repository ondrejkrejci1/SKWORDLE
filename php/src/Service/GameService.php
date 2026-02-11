<?php

namespace App\Service;

use App\Document\DailyPuzzle;
use App\Document\Teacher;
use Doctrine\ODM\MongoDB\DocumentManager;
use DateTime;

/**
 * Handles the core logic of the Skwordle game.
 * Manages daily puzzle generation and guess validation.
 */
class GameService
{
    private DocumentManager $dm;

    /**
     * Dependency injection for the DocumentManager.
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Retrieves the puzzle for the current day.
     * If no puzzle exists, it randomly selects a teacher and creates one.
     * * @return DailyPuzzle The puzzle document for today.
     */
    public function getTodaysPuzzle(): DailyPuzzle
    {
        $today = new DateTime('today');
        
        $puzzle = $this->dm->getRepository(DailyPuzzle::class)->findOneBy(['date' => $today]);

        if ($puzzle) {
            return $puzzle;
        }

        $teachers = $this->dm->getRepository(Teacher::class)->findAll();
        
        if (empty($teachers)) {
            throw new \RuntimeException('No teachers found in database to generate a puzzle.');
        }

        $randomTeacher = $teachers[array_rand($teachers)];
        $newPuzzle = new DailyPuzzle($today, $randomTeacher);

        $this->dm->persist($newPuzzle);
        $this->dm->flush();

        return $newPuzzle;
    }

    /**
 * Evaluates the guess while normalizing characters to ignore accents.
 * * @param string $guess The user input.
 * @param string $target The correct name from database.
 * @return array The color-coded result.
 */
public function evaluateGuess(string $guess, string $target): array
{
    /**
     * Internal function to remove accents and convert to uppercase.
     */
    $normalize = function($str) {
        $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; Upper()');
        return $transliterator->transliterate($str);
    };

    $guessNorm = $normalize($guess);
    $targetNorm = $normalize($target);
    
    $guessArr = mb_str_split($guessNorm, 1, 'UTF-8');
    $targetArr = mb_str_split($targetNorm, 1, 'UTF-8');
    
    $result = [];
    $remainingTargetChars = $targetArr;

    /**
     * First pass: Mark correct positions (Green).
     */
    foreach ($guessArr as $i => $char) {
        if (isset($targetArr[$i]) && $char === $targetArr[$i]) {
            $result[$i] = 'correct';
            unset($remainingTargetChars[$i]);
        } else {
            $result[$i] = null;
        }
    }

    /**
     * Second pass: Mark present characters (Yellow) or absent (Gray).
     */
    foreach ($guessArr as $i => $char) {
        if ($result[$i] === 'correct') continue;

        $foundIndex = array_search($char, $remainingTargetChars);
        if ($foundIndex !== false) {
            $result[$i] = 'present';
            unset($remainingTargetChars[$foundIndex]);
        } else {
            $result[$i] = 'absent';
        }
    }

    ksort($result);
    return $result;
}
}