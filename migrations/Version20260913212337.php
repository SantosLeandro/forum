<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913212337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $now = new \DateTimeImmutable();

        // Categorias
        $this->connection->insert('categories', [
            'title' => 'Geral',
            'position' => 1,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        $geralId = (int) $this->connection->lastInsertId();

        $this->connection->insert('categories', [
            'title' => 'Consoles e Jogos',
            'position' => 2,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        $consolesId = (int) $this->connection->lastInsertId();

        // Fóruns - Geral
        $this->connection->insert('forums', [
            'category_id' => $geralId,
            'title' => 'Notícias',
            'description' => 'Comente as novidades da semana',
            'position' => 1,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        // Fóruns - Consoles e Jogos
        $forums = [
            [
                'title' => 'PC',
                'description' => 'Dicas, análises e jogos para computadores',
                'position' => 1,
            ],
            [
                'title' => 'PlayStation',
                'description' => 'Dicas, análises e jogos da Sony',
                'position' => 2,
            ],
            [
                'title' => 'Xbox',
                'description' => 'Dicas, análises e jogos da Microsoft',
                'position' => 3,
            ],
            [
                'title' => 'Nintendo',
                'description' => 'Dicas, análises e jogos da Nintendo',
                'position' => 4,
            ],
            [
                'title' => 'Museu do videogame',
                'description' => 'Emuladores, clássicos e lembranças',
                'position' => 5,
            ],
        ];

        foreach ($forums as $forum) {
            $this->connection->insert('forums', [
                'category_id' => $consolesId,
                'title' => $forum['title'],
                'description' => $forum['description'],
                'position' => $forum['position'],
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);
        }

    }

    public function down(Schema $schema): void
    {
        $forumTitles = [
            'Notícias',
            'PC',
            'PlayStation',
            'Xbox',
            'Nintendo',
            'Museu do videogame',
        ];

        foreach ($forumTitles as $title) {
            $this->addSql(
                'DELETE FROM forums WHERE title = ?',
                [$title]
            );
        }

        $this->addSql(
            'DELETE FROM categories WHERE title IN (?, ?)',
            ['Geral', 'Consoles e Jogos']
        );

    }
}
