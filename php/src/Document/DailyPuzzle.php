<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DateTimeInterface;

/**
 * Stores the selected teacher for a specific date.
 * Ensures all players guess the same name on the same day.
 */
#[ODM\Document(collection: 'daily_puzzles')]
class DailyPuzzle
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'date')]
    #[ODM\Index(unique: true)]
    private DateTimeInterface $date;

    #[ODM\ReferenceOne(targetDocument: Teacher::class)]
    private Teacher $teacher;

    /**
     * Creates a new puzzle assignment.
     * * @param DateTimeInterface $date The date of the puzzle.
     * @param Teacher $teacher The teacher selected for this date.
     */
    public function __construct(DateTimeInterface $date, Teacher $teacher)
    {
        $this->date = $date;
        $this->teacher = $teacher;
    }

    /**
     * Retrieves the teacher assigned to this puzzle.
     */
    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }
}