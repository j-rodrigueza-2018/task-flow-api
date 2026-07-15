-- Add board_id column to tasks table as mandatory.
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS board_id UUID NOT NULL;

-- Remove the existing foreign key constraint on board_id if it exists.
ALTER TABLE tasks DROP CONSTRAINT IF EXISTS fk_tasks_board_id;

-- Add a new foreign key constraint on board_id referencing boards table with ON DELETE CASCADE.
ALTER TABLE tasks ADD CONSTRAINT fk_tasks_board_id 
    FOREIGN KEY (board_id) 
    REFERENCES boards(id)
    ON DELETE CASCADE;