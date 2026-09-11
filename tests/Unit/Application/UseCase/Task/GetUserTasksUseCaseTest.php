<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Task;

use App\Application\UseCase\Task\GetUserTasksUseCase;
use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetUserTasksUseCaseTest extends TestCase
{
    private TaskRepository&MockObject $task_repository_mock;
    private GetUserTasksUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->task_repository_mock = $this->createMock(TaskRepository::class);
        $this->use_case = new GetUserTasksUseCase(
            taskRepository: $this->task_repository_mock
        );
    }

    public function testItThrowsExceptionIfUserIdIsEmpty(): void
    {
        $this->task_repository_mock
            ->expects($this->never())
            ->method('findByUserId');

        try {
            $this->use_case->execute('');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The user_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItReturnsUserTasksSuccessfully(): void
    {
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_task_1 = new Task(
            id: uuid_create(UUID_TYPE_RANDOM),
            title: 'Task 1',
            description: 'Description for Task 1',
            status: Task::STATUS_PENDING,
            board_id: $board_id,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_task_2 = new Task(
            id: uuid_create(UUID_TYPE_RANDOM),
            title: 'Task 2',
            description: 'Description for Task 2',
            status: Task::STATUS_PENDING,
            board_id: $board_id,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->task_repository_mock
            ->expects($this->once())
            ->method('findByUserId')
            ->with($user_id)
            ->willReturn([$dummy_task_1, $dummy_task_2]);

        $result = $this->use_case->execute($user_id);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($dummy_task_1, $result[0]);
        $this->assertSame($dummy_task_2, $result[1]);
    }
}
