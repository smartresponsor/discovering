<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260923192125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_feedback (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              resource VARCHAR(64) NOT NULL,
              hit_id VARCHAR(255) NOT NULL,
              title VARCHAR(255) DEFAULT '' NOT NULL,
              reference VARCHAR(255) DEFAULT '' NOT NULL,
              click_count INTEGER NOT NULL,
              last_clicked_at VARCHAR(64) DEFAULT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_discovery_feedback_resource_hit ON discovery_feedback (resource, hit_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_index_alias (
              alias VARCHAR(191) NOT NULL,
              target VARCHAR(191) NOT NULL,
              PRIMARY KEY (alias)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_index_document (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              index_name VARCHAR(191) NOT NULL,
              document_id VARCHAR(191) NOT NULL,
              title VARCHAR(255) NOT NULL,
              resource VARCHAR(191) NOT NULL,
              reference VARCHAR(255) NOT NULL,
              status VARCHAR(64) NOT NULL,
              content CLOB NOT NULL,
              updated_at VARCHAR(64) NOT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_discovery_index_document ON discovery_index_document (index_name, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_libsource_operator_event_log (
              event_id VARCHAR(40) NOT NULL,
              event_name VARCHAR(128) NOT NULL,
              level VARCHAR(32) NOT NULL,
              summary CLOB NOT NULL,
              context_json CLOB NOT NULL,
              PRIMARY KEY (event_id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_operation_event_log (
              event_id VARCHAR(40) NOT NULL,
              request_id VARCHAR(128) NOT NULL,
              channel VARCHAR(64) NOT NULL,
              operation VARCHAR(191) NOT NULL,
              status VARCHAR(32) NOT NULL,
              occurred_at VARCHAR(64) NOT NULL,
              context_json CLOB NOT NULL,
              PRIMARY KEY (event_id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_rate_limit_bucket (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              bucket VARCHAR(255) NOT NULL,
              bucket_count INTEGER NOT NULL,
              reset_at INTEGER NOT NULL
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_discovery_rate_limit_bucket ON discovery_rate_limit_bucket (bucket)');
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_rebuild_evidence (
              evidence_id VARCHAR(128) NOT NULL,
              resource VARCHAR(64) NOT NULL,
              finished_at VARCHAR(64) NOT NULL,
              payload_json CLOB NOT NULL,
              PRIMARY KEY (evidence_id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE discovery_search_log (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              term VARCHAR(128) NOT NULL,
              customer_reference VARCHAR(64) DEFAULT NULL,
              filters CLOB DEFAULT NULL,
              uuid BLOB NOT NULL,
              slug VARCHAR(190) NOT NULL,
              created_at DATETIME NOT NULL,
              modified_at DATETIME DEFAULT NULL,
              created_by VARCHAR(190) DEFAULT NULL,
              modified_by VARCHAR(190) DEFAULT NULL
            )
        SQL);
        $this->addSql('CREATE INDEX idx_discovery_search_log_term ON discovery_search_log (term)');
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_discovery_search_log_customer_reference ON discovery_search_log (customer_reference)
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_discovery_search_log_uuid ON discovery_search_log (uuid)');
        $this->addSql('CREATE UNIQUE INDEX uniq_discovery_search_log_slug ON discovery_search_log (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE discovery_feedback');
        $this->addSql('DROP TABLE discovery_index_alias');
        $this->addSql('DROP TABLE discovery_index_document');
        $this->addSql('DROP TABLE discovery_libsource_operator_event_log');
        $this->addSql('DROP TABLE discovery_operation_event_log');
        $this->addSql('DROP TABLE discovery_rate_limit_bucket');
        $this->addSql('DROP TABLE discovery_rebuild_evidence');
        $this->addSql('DROP TABLE discovery_search_log');
    }
}
