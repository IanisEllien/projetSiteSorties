<?php

namespace App\Data;

use App\Entity\Campus;
use DateTime;

class FiltreSortie
{

    /**
     * @var string
     */
    public $q = '';

    /**
     * @var Campus
     */
    public $campus;

    /**
     * @var DateTime
     */
    public $dateMin;

    /**
     * @var DateTime
     */
    public $dateMax;

    /**
     * @var string[]
     */
    /*public $typeSortie = ['Sorties dont je suis l\'organisateur/trice',
                          'Sorties auxquelles je suis inscrit/e',
                          'Sorties auxquelles je ne suis pas inscrit/e',
                          'Sorties passées'];
*/
    public $typeSortie = [];


}