<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Board;
use App\Domain\Entity\BoardUser;

interface BoardRepository
{
    /**
     * Saves a board to the repository.
     * 
     * @param Board $board The board to save.
     * 
     * @return void
     */
    public function save(Board $board): void;

    /**
     * Deletes a board from the repository.
     * 
     * @param Board $board The board to delete.
     * 
     * @return void
     */
    public function delete(Board $board): void;

    /**
     * Finds a board by its ID.
     * 
     * @param string $id The ID of the board to find.
     * 
     * @return Board|null The found board or null if not found.
     */
    public function findById(string $id): ?Board;

    /**
     * Finds all boards in the repository.
     * 
     * @return Board[] An array of all boards.
     */
    public function findAll(): array;

    /**
     * Associates a user to a board.
     * 
     * @param BoardUser $board_user The entity that represents the relationship.
     * 
     * @return void
     */
    public function addUserToBoard(BoardUser $board_user): void;
}
