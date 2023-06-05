<?php

namespace JoeDixon\TranslationCore\Providers\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JoeDixon\TranslationCore\Providers\Eloquent\Factories\LanguageFactory;

class Language extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('translation.database.connection');
        $this->table = config('translation.database.languages_table');
    }

    /**
     * Instantiate a new factory instance for the model.
     */
    protected static function newFactory(): LanguageFactory
    {
        return new LanguageFactory;
    }

    /**
     * Get the translations for the language.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }
}
