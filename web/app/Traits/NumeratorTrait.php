<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait NumeratorTrait
{
    protected static function bootNumeratorTrait()
    {
        // generate the document number when creating a new document
        self::creating(function ($model) {
            // generate new document number while the generated one exists
            do {
                $number = $model->getRandomChar();
            } while (self::where('number', $number)->first());

            $model->setAttribute('number', (string)$number);
        });
    }

    /**
     * Get the numerator prefix for the model.
     *
     * @return string
     */
    protected function getNumeratorPrefix(): string
    {
        return 'DC';
    }

    /**
     * @return string
     */
    protected function getRandomChar(): string
    {
        $string = Str::upper(Str::random(12));

        return sprintf("%s-%s", $this->getNumeratorPrefix(), $string);
    }
}
