<?php

namespace JoeDixon\TranslationCore\Providers\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JoeDixon\TranslationCore\Providers\Eloquent\Factories\TranslationFactory;

/**
 * \JoeDixon\TranslationCore\Providers\Eloquent\Translation.
 *
 * @property int $id
 * @property int $language_id
 * @property string|null $vendor
 * @property string|null $group
 * @property string $key
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Translation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('translation.database.connection');
        $this->table = config('translation.database.translations_table');
    }

    /**
     * Instantiate a new factory instance for the model.
     */
    protected static function newFactory(): TranslationFactory
    {
        return new TranslationFactory;
    }

    /**
     * Get the language for this translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public static function getGroupsForLanguage($language)
    {
        return static::whereHas('language', function ($q) use ($language) {
            $q->where('language', $language);
        })->whereNotNull('group')
            ->where('group', 'not like', '%single')
            ->select('group')
            ->distinct()
            ->get();
    }
}
