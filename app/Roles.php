<?php

namespace App;

enum Roles: string
{
    case LEADAUTHOR = "leadauthor";
    case AUTHOR = "author";
    case PROJECTMANAGER = "projectmanager";
    case EDITOR = "editor";

    public function info(): array
    {
        return match ($this) {
            self::LEADAUTHOR => ['name' => 'lead author', 'description' => 'Allows full control in the project.'],
            self::AUTHOR => ['name' => 'author', 'description' => 'Allows only writing-oriented privileges.'],
            self::PROJECTMANAGER => ['name' => 'project manager', 'description' => 'Allows project control without writing privileges.'],
            self::EDITOR => ['name' => 'editor', 'description' => 'Allows only reading and commenting privileges.'],
        };
    }
}
