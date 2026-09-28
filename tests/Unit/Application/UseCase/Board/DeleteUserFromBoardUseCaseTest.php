<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\DeleteUserFromBoardUseCase;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteUserFromBoardUseCaseTest extends TestCase
{
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private DeleteUserFromBoardUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);
        $this->use_case = new DeleteUserFromBoardUseCase($this->board_user_repository_mock);
    }

    public function testItThrowsExceptionIfBoardIdIsEmpty(): void
    {
        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute('', uuid_create(UUID_TYPE_RANDOM), uuid_create(UUID_TYPE_RANDOM));
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The board_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserIdIsEmpty(): void
    {
        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), '', uuid_create(UUID_TYPE_RANDOM));
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The user_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterIdIsEmpty(): void
    {
        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), uuid_create(UUID_TYPE_RANDOM), '');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The requester_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserIsNotMember(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $user_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The user is not a member of this board.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfOwnerTriesToLeave(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = $user_id;

        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::OWNER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn($dummy_board_user);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $user_id, $requester_id);
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $exception) {
            $this->assertEquals('An owner cannot leave the board. You must delete the board or transfer ownership.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterIsNotMember(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(function (string $b_id, string $u_id) use ($requester_id, $user_id, $dummy_board_member) {
                return match ($u_id) {
                    $user_id => $dummy_board_member,
                    $requester_id => null, // El solicitante no pertenece
                    default => null,
                };
            });

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $user_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The requester is not a member of this board.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterLacksPermission(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_requester_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(function (string $b_id, string $u_id) use ($requester_id, $user_id, $dummy_board_member, $dummy_requester_member) {
                return match ($u_id) {
                    $user_id => $dummy_board_member,
                    $requester_id => $dummy_requester_member,
                    default => null,
                };
            });

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $user_id, $requester_id);
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $exception) {
            $this->assertEquals('Only owners and admins can remove other members from the board.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfAdminTriesToRemoveOwner(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::OWNER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_requester_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::ADMIN,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(function (string $b_id, string $u_id) use ($requester_id, $user_id, $dummy_board_member, $dummy_requester_member) {
                return match ($u_id) {
                    $user_id => $dummy_board_member,
                    $requester_id => $dummy_requester_member,
                    default => null,
                };
            });

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $user_id, $requester_id);
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $exception) {
            $this->assertEquals('An owner cannot be removed from the board.', $exception->getMessage());
        }
    }

    public function testItDeletesUserWhenUserIsRemovingThemselves(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = $user_id;

        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn($dummy_board_user);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('delete')
            ->with($board_id, $user_id);

        $this->use_case->execute($board_id, $user_id, $requester_id);
    }

    public function testItDeletesUserWhenRequesterIsAdmin(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_requester_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::ADMIN,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(function (string $b_id, string $u_id) use ($requester_id, $user_id, $dummy_board_member, $dummy_requester_member) {
                return match ($u_id) {
                    $user_id => $dummy_board_member,
                    $requester_id => $dummy_requester_member,
                    default => null,
                };
            });

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('delete')
            ->with($board_id, $user_id);

        $this->use_case->execute($board_id, $user_id, $requester_id);
    }

    public function testItDeletesUserWhenRequesterIsOwner(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_requester_member = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::OWNER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(function (string $b_id, string $u_id) use ($requester_id, $user_id, $dummy_board_member, $dummy_requester_member) {
                return match ($u_id) {
                    $user_id => $dummy_board_member,
                    $requester_id => $dummy_requester_member,
                    default => null,
                };
            });

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('delete')
            ->with($board_id, $user_id);

        $this->use_case->execute($board_id, $user_id, $requester_id);
    }
}
