<?php

namespace App\Command;

use App\Document\Teacher;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commands to populate the MongoDB with the provided list of teachers.
 * This script strips academic titles and keeps only the surname.
 */
#[AsCommand(name: 'app:seed-teachers', description: 'Populates the database with the provided teacher list')]
class SeedTeachersCommand extends Command
{
    private DocumentManager $dm;

    /**
     * Dependency injection for the Document Manager.
     */
    public function __construct(DocumentManager $dm)
    {
        parent::__construct();
        $this->dm = $dm;
    }

    /**
     * Extracts surnames from the raw teacher strings and saves them.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rawList = [
            'Bc. Daniel Adámek', 'Ing. Anna Bodnárová', 'Ing. Lucie Brčáková', 'Mgr. Lenka Brůnová',
            'Ing. Peter Budai', 'RNDr. Mgr. Petr Couf', 'Ing. Richard Černý, CSc.', 'Pavel Dočkal, DiS.',
            'Ing. Jana Exnerová', 'Kateřina Haasová', 'Mgr. Jan Hehl', 'Doc. Ing. Aleš Herman, PhD.',
            'Ing. Jiří Herout', 'Adam Horyna', 'Mgr. Libuše Hrabalová', 'Mgr. Iulia Iarosciuc',
            'Mgr. et Mgr. Martin Janečka, Ph.D.', 'David Janoušek', 'Ing. Jiří Jedlička',
            'Doc. Ing. Vítězslav Jeřábek, CSc.', 'Mgr. Zbyněk Ježek', 'Mgr. Ondřej Ježil',
            'Ing. Tomáš Juchelka', 'Ing. Filip Kallmünzer', 'Ing. Ivana Kantnerová', 'Mgr. Svatava Klemová',
            'Tomáš Klíma', 'Mgr. Marie Kmoníčková', 'Ing. Zdeněk Křída', 'Ing. Dušan Kuchařík',
            'Mgr. Jan Kuchařík', 'RNDr. Olga Kvapilová', 'Bc. Marek Lavička', 'Bc. Kateřina Lešková',
            'Mgr. Pavel Lopocha', 'Ing. Ondřej Mandík', 'Ing. Lukáš Masopust', 'PhDr. Jakub Mazuch',
            'Michaela Meitnerová', 'Jan Molič', 'Mgr. Jindřiška Mrázová', 'Mgr. Martina Mušecová',
            'Vratislav Němec', 'Mgr. Eva Neugebauerová', 'Ing. Jan Novotný, Ph.D.', 'Ing. Bc. Šárka Páltiková',
            'Bc. Adam Papula', 'Ing. Martin Peter', 'Petr Procházka', 'RNDr. Luboš Rašek',
            'Mgr. Alena Reichlová', 'Mgr. Jitka Rychlíková', 'Ing. Oleg Sivkov, Ph.D',
            'MUDr. Kristina Studénková', 'Ing. Vladislav Sýkora', 'Matěj Šedivý', 'Ing. Jana Šedová',
            'Ing. Jan Šváb', 'Bc. Karolína Uhlířová', 'Ing. Mgr. Vladimír Váňa, CSc.', 'Bc. Martin Váňa',
            'Ing. Jan Vaněk', 'Ing. Zdeněk Velich', 'Ing. Jiří Vlček', 'Ing. Antonín Vobecký',
            'Ing. Zdeněk Vondra', 'Mgr. David Weber', 'Ing. Jan Zelenka', 'Mgr. Tomáš Žilinčár'
        ];

        $count = 0;
        foreach ($rawList as $rawName) {
            $cleanName = $this->extractSurname($rawName);
            
            if ($cleanName) {
                $teacher = new Teacher($cleanName);
                $this->dm->persist($teacher);
                $count++;
            }
        }

        $this->dm->flush();

        $io->success("Successfully imported $count teachers into MongoDB!");

        return Command::SUCCESS;
    }

    /**
     * Strips academic titles and common suffixes to isolate the surname.
     * * @param string $fullName The full name with titles.
     * @return string|null The detected surname.
     */
    private function extractSurname(string $fullName): ?string
    {
        $titles = ['Bc.', 'Ing.', 'Mgr.', 'RNDr.', 'Doc.', 'PhD.', 'CSc.', 'DiS.', 'PhDr.', 'MUDr.', 'et', ','];
        
        $parts = explode(' ', $fullName);
        $cleanParts = array_filter($parts, function($part) use ($titles) {
            $part = trim($part, ',');
            return !in_array($part, $titles);
        });

        $cleanParts = array_values($cleanParts);

        if (count($cleanParts) >= 2) {
            return $cleanParts[1];
        }

        return $cleanParts[0] ?? null;
    }
}