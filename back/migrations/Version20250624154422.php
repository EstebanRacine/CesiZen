<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624154422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE emotion ADD categorie_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emotion ADD CONSTRAINT FK_DEBC77BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_emotion (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DEBC77BCF5E72D ON emotion (categorie_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info ADD menu_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info ADD CONSTRAINT FK_CB893157CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CB893157CCD7E912 ON info (menu_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tracker ADD emotion_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tracker ADD CONSTRAINT FK_AC632AAF1EE4A582 FOREIGN KEY (emotion_id) REFERENCES emotion (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AC632AAF1EE4A582 ON tracker (emotion_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE emotion DROP FOREIGN KEY FK_DEBC77BCF5E72D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_DEBC77BCF5E72D ON emotion
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emotion DROP categorie_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tracker DROP FOREIGN KEY FK_AC632AAF1EE4A582
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AC632AAF1EE4A582 ON tracker
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tracker DROP emotion_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info DROP FOREIGN KEY FK_CB893157CCD7E912
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CB893157CCD7E912 ON info
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info DROP menu_id
        SQL);
    }
}
