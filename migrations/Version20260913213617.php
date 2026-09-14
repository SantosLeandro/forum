<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913213617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $now = new \DateTimeImmutable(); 
        $nowString = $now->format('Y-m-d H:i:s');
        $this->connection->insert('users', ['email' => 'admin@localhost', 'roles' => '["ROLE_ADMIN"]', 'password' => 'COLE_AQUI_O_HASH_DA_SENHA', 'username' => 'admin', 'avatar' => null, 'created_at' => $nowString, 'updated_at' => $nowString, 'deleted_at' => null,]);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
