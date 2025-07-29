<?php

namespace App;

enum Roles: string
{
    case AUTHOR = "author";
    case EDITOR = "editor";
    case LEADAUTHOR = "leadauthor";
    case PROJECTMANAGER = "projectmanager";

    public function info(): array
    {
        return match ($this) {
            self::AUTHOR => ['name' => 'author', 'description' => 'Allows only writing-oriented privileges.'],
            self::LEADAUTHOR => ['name' => 'lead author', 'description' => 'Allows full control in the project.'],
            self::EDITOR => ['name' => 'editor', 'description' => 'Allows only reading and commenting privileges.'],
            self::PROJECTMANAGER => ['name' => 'project manager', 'description' => 'Allows project control without writing privileges.'],
        };
    }
}
