<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration volontairement vide.
 *
 * Elle était à l'origine un dump complet qui recréait des tables déjà créées par
 * Version20260823055522 (centre_jeune, emission, quiz…) et réajoutait des colonnes
 * déjà ajoutées par Version20260903103019 (fil réseaux sociaux) et
 * Version20260910155215 (emission.fichier_audio_name). Toute installation neuve
 * échouait donc. La classe est conservée pour que les bases qui l'ont déjà
 * exécutée gardent un historique cohérent.
 */
final class Version20261118085648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration vide (doublon des migrations 20260823055522, 20260903103019 et 20260910155215)';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }
}
