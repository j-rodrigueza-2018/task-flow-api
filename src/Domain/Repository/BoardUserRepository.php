<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\BoardUser;

interface BoardUserRepository
{
    /**
     * Saves or updates the relationship between a user with a board.
     * 
     * @param BoardUser $board_user The relation to save.
     * 
     * @return void
     */
    public function save(BoardUser $board_user): void;

    /**
     * Deletes the relationship between a user and a board.
     * 
     * @param string $board_id The ID of the board.
     * @param string $user_id The ID of the user.
     * 
     * @return void
     */
    public function delete(string $board_id, string $user_id): void;

    /**
     * Finds the relationship between a user with a board by board's ID and user's ID.
     * 
     * @param string $board_id The ID of the board.
     * @param string $user_id The ID of the user.
     * 
     * @return BoardUser|null The found relationship or null if not found.
     */
    public function findByBoardAndUser(string $board_id, string $user_id): ?BoardUser;
}
