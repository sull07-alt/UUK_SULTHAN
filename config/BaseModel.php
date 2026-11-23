<?php
abstract class BaseModel
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    abstract public function getAll(): array;
}
