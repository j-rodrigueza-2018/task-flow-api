<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardUserRepository;
use DomainException;
use InvalidArgumentException;

final class DeleteUserFromBoardUseCase
{
    public function __construct(
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $board_id, string $user_id, string $requester_id): void
    {
        if (empty($board_id)) {
            throw new InvalidArgumentException('The board_id cannot be empty.');
        }

        if (empty($user_id)) {
            throw new InvalidArgumentException('The user_id cannot be empty.');
        }

        if (empty($requester_id)) {
            throw new InvalidArgumentException('The requester_id cannot be empty.');
        }

        $target_member = $this->board_user_repository->findByBoardAndUser($board_id, $user_id);
        if (!$target_member) {
            throw new InvalidArgumentException('The user is not a member of this board.');
        }

        if ($user_id === $requester_id) {
            if ($target_member->getRole() === BoardRole::OWNER) {
                throw new DomainException('An owner cannot leave the board. You must delete the board or transfer ownership.');
            }
        } else {
            $requester_member = $this->board_user_repository->findByBoardAndUser($board_id, $requester_id);
            if (!$requester_member) {
                throw new InvalidArgumentException('The requester is not a member of this board.');
            }

            if (!in_array($requester_member->getRole(), [BoardRole::OWNER, BoardRole::ADMIN], true)) {
                throw new DomainException('Only owners and admins can remove other members from the board.');
            }

            if ($target_member->getRole() === BoardRole::OWNER) {
                throw new DomainException('An owner cannot be removed from the board.');
            }
        }

        $this->board_user_repository->delete($board_id, $user_id);
    }
}
