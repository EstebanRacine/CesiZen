<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616160008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categorie_emotion (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, couleur VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE emotion (id INT AUTO_INCREMENT NOT NULL, dernier_modificateur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, icone VARCHAR(255) NOT NULL, actif TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_suppression DATETIME DEFAULT NULL, INDEX IDX_DEBC771C55BC7A (dernier_modificateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE info (id INT AUTO_INCREMENT NOT NULL, createur_id INT NOT NULL, modificateur_id INT DEFAULT NULL, supprimeur_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, actif TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, date_suppression DATETIME DEFAULT NULL, INDEX IDX_CB89315773A201E5 (createur_id), INDEX IDX_CB893157D3DF658 (modificateur_id), INDEX IDX_CB89315776658FC2 (supprimeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, dernier_modificateur_id INT NOT NULL, nom VARCHAR(255) NOT NULL, icone VARCHAR(255) NOT NULL, actif TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_suppression DATETIME DEFAULT NULL, INDEX IDX_7D053A931C55BC7A (dernier_modificateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tracker (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, datetime DATETIME NOT NULL, commentaire VARCHAR(255) DEFAULT NULL, actif TINYINT(1) NOT NULL, INDEX IDX_AC632AAFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, actif TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_supression DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emotion ADD CONSTRAINT FK_DEBC771C55BC7A FOREIGN KEY (dernier_modificateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info ADD CONSTRAINT FK_CB89315773A201E5 FOREIGN KEY (createur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info ADD CONSTRAINT FK_CB893157D3DF658 FOREIGN KEY (modificateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info ADD CONSTRAINT FK_CB89315776658FC2 FOREIGN KEY (supprimeur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu ADD CONSTRAINT FK_7D053A931C55BC7A FOREIGN KEY (dernier_modificateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tracker ADD CONSTRAINT FK_AC632AAFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE emotion DROP FOREIGN KEY FK_DEBC771C55BC7A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info DROP FOREIGN KEY FK_CB89315773A201E5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info DROP FOREIGN KEY FK_CB893157D3DF658
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info DROP FOREIGN KEY FK_CB89315776658FC2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu DROP FOREIGN KEY FK_7D053A931C55BC7A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tracker DROP FOREIGN KEY FK_AC632AAFA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categorie_emotion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE emotion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE info
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE menu
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tracker
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
