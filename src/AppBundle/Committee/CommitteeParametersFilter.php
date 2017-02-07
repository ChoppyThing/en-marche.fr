<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class CommitteeParametersFilter
{
    /**
     * Parse a Json string to find uuids.
     *
     * @param string $json
     *
     * @return Uuid[]
     */
    public static function getUuidsFromJson(string $json): array
    {
        $json = json_decode($json, true);

        if (!is_array($json)) {
            return [];
        }

        foreach ($json as $row) {
            try {
                $uuids[] = Uuid::fromString($row);
            } catch (\Exception $exception) {
                // Drop the uuid
            }
        }

        return $uuids ?? [];
    }

    /**
     * @param Uuid[]     $uuids
     * @param Adherent[] $adherents
     *
     * @return Adherent[]
     */
    public static function removeUnknownAdherents(array $uuids, $adherents): array
    {
        foreach ($uuids as $uuid) {
            foreach ($adherents as $adherent) {
                if ((string) $adherent->getUuid() === (string) $uuid) {
                    $keeps[] = $adherent;
                }
            }
        }

        return $keeps ?? [];
    }
}
