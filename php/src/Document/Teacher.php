<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Represents a teacher in the school database.
 * This collection serves as the dictionary for the game.
 */
#[ODM\Document(collection: 'teachers')]
class Teacher
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    #[ODM\Index(unique: true)]
    private string $name;

    #[ODM\Field(type: 'collection')]
    private array $subjects = [];

    /**
     * Initializes the teacher with a name and optional subjects.
     * * @param string $name The surname of the teacher.
     * @param array $subjects List of subjects taught (e.g., ['Matematika', 'Fyzika']).
     */
    public function __construct(string $name, array $subjects = [])
    {
        $this->name = mb_strtoupper($name, 'UTF-8');
        $this->subjects = $subjects;
    }

    /**
     * Returns the unique identifier of the teacher.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Returns the normalized name of the teacher.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the length of the teacher's name for grid generation.
     */
    public function getNameLength(): int
    {
        return mb_strlen($this->name, 'UTF-8');
    }
}