<?php

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * API endpoints for the Skwordle game frontend.
 */
#[Route('/api', name: 'api_')]
class GameController extends AbstractController
{
    private GameService $gameService;

    /**
     * Injecting the game service.
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Endpoint to initialize the game board.
     * Returns the length of the word so the frontend can draw the grid.
     * * @return JsonResponse Length of the name and puzzle ID.
     */
    #[Route('/init', methods: ['GET'])]
    public function init(): JsonResponse
    {
        $puzzle = $this->gameService->getTodaysPuzzle();
        $teacher = $puzzle->getTeacher();

        return $this->json([
            'date' => date('Y-m-d'),
            'wordLength' => $teacher->getNameLength(),
        ]);
    }

    /**
     * Endpoint to check a user's guess.
     * * @param Request $request Contains the JSON payload with the guess.
     * @return JsonResponse Evaluation result.
     */
    #[Route('/guess', methods: ['POST'])]
    public function guess(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $guess = $data['guess'] ?? '';

        $puzzle = $this->gameService->getTodaysPuzzle();
        $targetName = $puzzle->getTeacher()->getName();

        if (mb_strlen($guess, 'UTF-8') !== mb_strlen($targetName, 'UTF-8')) {
            return $this->json(['error' => 'Invalid word length'], 400);
        }

        $evaluation = $this->gameService->evaluateGuess($guess, $targetName);
        $isWin = $guess === $targetName;

        return $this->json([
            'evaluation' => $evaluation,
            'solved' => $isWin,
            'correctWord' => $isWin ? $targetName : null
        ]);
    }
}