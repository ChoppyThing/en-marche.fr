<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;

class CommitteeExportTransformer
{
    /**
     * @param Adherent[] $adherents
     *
     * @return string
     *
     * @throws \BadMethodCallException
     */
    public static function getAdherentsAsCsv(array $adherents)
    {
        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, ['Prénom', 'Nom', 'Age', 'Ville', 'Pays', 'Adresse email']);

        foreach ($adherents as $adherent) {
            if (!$adherent instanceof Adherent) {
                throw new \BadMethodCallException('This method requires a collection of Adherent entities');
            }

            fputcsv($handle, [
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $adherent->getAge(),
                $adherent->getCityName(),
                $adherent->getCountry(),
                $adherent->getEmailAddress(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
