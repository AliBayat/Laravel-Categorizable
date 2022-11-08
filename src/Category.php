<?php

declare(strict_types=1);

/**
 * Laravel Categorizable Package by Ali Bayat.
 */

namespace AliBayat\LaravelCategorizable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use NodeTrait, HasSlug;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return mixed
     */
    public function categories(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * @return Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function entries(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'model', 'categories_models');
    }
    
    /**
     * @returns : Illuminate\Database\Eloquent\Builder
     */
    public function allEntries($class)
    {
        $table = app($class)->getTable();

        return $class::join('categories_models', 'categories_models.model_id', '=', "{$table}.id")
            ->where('categories_models.category_id', $this->id)
            ->orWhereIn(
                'categories_models.category_id', 
                $this->descendants()->pluck('id')->toArray()
            )
            ->select("{$table}.*", 'category_id');
    }    

    /**
     * @return array
     */
    public static function tree(): array
    {
        return static::get()->toTree()->toArray();
    }

    /**
     * @return static
     */
    public static function findByName(string $name): self
    {
        return static::where('name', $name)->orWhere('slug', $name)->firstOrFail();
    }

    /**
     * @return static
     */
    public static function findById(int $id): self
    {
        return static::findOrFail($id);
    }

    /**
     * Spatie sluggable options
     */    
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
    
    /**
     * Generate a non unique slug for the record.
     */
    protected function generateNonUniqueSlug(): string
    {
        if ($this->hasCustomSlugBeenUsed()) {
            $slugField = $this->slugOptions->slugField;

            return $this->$slugField;
        }

        return $this->persianSlug(
            $this->getSlugSourceString(), 
            $this->slugOptions->slugSeparator
        );
    }
    
    /**
     * Generate the persian slug if necessary.
     */
    protected function persianSlug($title, $separator = '-'): string
    {
        $title = trim($title);
        $title = mb_strtolower($title, 'UTF-8');
        $title = str_replace('‌', $separator, $title);
        $title = preg_replace(
            '/[^a-z0-9_\s\-اآؤئبپتثجچحخدذرزژسشصضطظعغفقكکگلمنوةيإأۀءهی۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩]/u',
            '',
            $title
        );
        $title = preg_replace('/[\s\-_]+/', ' ', $title);
        $title = preg_replace('/[\s_]/', $separator, $title);
        $title = trim($title, $separator);

        return $title;
    }    
}
