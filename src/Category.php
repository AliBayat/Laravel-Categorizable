<?php

declare(strict_types=1);

/**
 * Laravel Categorizable Package by Ali Bayat.
 */

namespace AliBayat\LaravelCategorizable;

use Illuminate\Database\Eloquent\{Builder, Model, Relations\MorphTo, Relations\MorphToMany};
use Exception;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Sluggable\{HasSlug, SlugOptions};
use RuntimeException;

class Category extends Model
{
    use NodeTrait;
	use HasSlug;
	
	/**
	 * @var string[]
	 */
    protected $guarded = ['id', 'created_at', 'updated_at'];
	
	/**
	 * @return MorphTo
	 */
    public function categories(): MorphTo
    {
        return $this->morphTo();
    }
	
	/**
	 * @param string $class
	 * @return MorphToMany
	 */
    public function entries(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'model', config('laravel-categorizable.table_names.morph_table'));
    }
	
	/**
	 * @param string $class
	 * @return Builder
	 * @throws Exception
	 */
    public function allEntries(string $class): Builder
    {
        $table = app($class)->getTable();
		$morphTable = config('laravel-categorizable.table_names.morph_table');
		if (!class_exists($class)) {
			throw new RuntimeException('the given model does not exist.');
		}
		return $class::join($morphTable, $morphTable . '.model_id', $table . '.id')
			->where($morphTable . '.category_id', $this->id)
			->orWhereIn($morphTable . '.category_id', $this->descendants()->pluck('id')->toArray())
			->select($table . '.*', 'category_id');
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
     * Fixes the persian slug if necessary.
     */
    protected function persianSlug($title, string $separator = '-'): string
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
	    return trim($title, $separator);
    }    
}
