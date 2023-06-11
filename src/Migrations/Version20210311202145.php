<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210311202145 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tagging MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED12359D8A214');
        $this->addSql('ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED123BAD26311');
        $this->addSql('ALTER TABLE tagging DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE tagging DROP id, CHANGE recipe_id recipe_id INT NOT NULL, CHANGE tag_id tag_id INT NOT NULL');
        $this->addSql('ALTER TABLE tagging ADD CONSTRAINT FK_A4AED12359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tagging ADD CONSTRAINT FK_A4AED123BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tagging ADD PRIMARY KEY (recipe_id, tag_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED12359D8A214');
        $this->addSql('ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED123BAD26311');
        $this->addSql('ALTER TABLE tagging ADD id INT AUTO_INCREMENT NOT NULL, CHANGE recipe_id recipe_id INT DEFAULT NULL, CHANGE tag_id tag_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE tagging ADD CONSTRAINT FK_A4AED12359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE tagging ADD CONSTRAINT FK_A4AED123BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
