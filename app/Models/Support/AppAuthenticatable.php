<?php

namespace App\Models\Support;

use DateTimeInterface;
use Illuminate\Foundation\Auth\User as EloquentAuthenticatable;

$useMongoDocumentModel = extension_loaded('mongodb')
    && (
        ($_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) === 'mongodb'
        || trim((string) ($_ENV['MONGODB_URI'] ?? $_SERVER['MONGODB_URI'] ?? getenv('MONGODB_URI'))) !== ''
        || trim((string) ($_ENV['DB_URI'] ?? $_SERVER['DB_URI'] ?? getenv('DB_URI'))) !== ''
    );

if ($useMongoDocumentModel) {
    abstract class AppAuthenticatable extends \MongoDB\Laravel\Auth\User
    {
        protected $connection = 'mongodb';

        protected function asDateTime($value): \Illuminate\Support\Carbon
        {
            $date = parent::asDateTime($value);
            $timezone = (string) config('app.timezone', 'UTC');

            if ($value instanceof DateTimeInterface && $timezone !== '') {
                return $date->setTimezone($timezone);
            }

            return $date;
        }
    }
} else {
    abstract class AppAuthenticatable extends EloquentAuthenticatable
    {
        protected function asDateTime($value): \Illuminate\Support\Carbon
        {
            $date = parent::asDateTime($value);
            $timezone = (string) config('app.timezone', 'UTC');

            if ($value instanceof DateTimeInterface && $timezone !== '') {
                return $date->setTimezone($timezone);
            }

            return $date;
        }
    }
}
