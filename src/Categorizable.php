<?php

declare(strict_types=1);

/**
 * Laravel Categorizable Package by Ali Bayat.
 */

namespace AliBayat\LaravelCategorizable;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Categorizable
{
    /**
     * @return string
     */
    public function categorizableModel(): string
    {
        return config('laravel-categorizable.models.category');
    }


    /**
     * @return mixed
     */
    public function categories(): MorphToMany
    {
        return $this->morphToMany(
            $this->categorizableModel(),
            'model',
	        config('laravel-categorizable.table_names.morph_table')
        );
    }

    /**
     * @return array
     */
    public function categoriesList(): array
    {
        return $this->categories()->pluck('name', 'id')->toArray();
    }
	
	/**
	 * @return array
	 */
    public function categoriesIds(): array
    {
        return $this->categories()->pluck('id')->toArray();
    }
	
	
	/**
	 * @param ...$categories
	 * @return $this
	 */
    public function attachCategory(...$categories): static
    {
        $categories = collect($categories)
            ->flatten()
            ->map(fn ($category) => $this->getStoredCategory($category))
            ->all();

        $this->categories()->saveMany($categories);

        return $this;
    }
	
	/**
	 * @param $category
	 * @return void
	 */
    public function detachCategory($category): void
    {
        $this->categories()->detach($this->getStoredCategory($category));
    }

    /**
     * @param $categories . list of params or an array of parameters
     *
     * @return mixed
     */
    public function syncCategories(...$categories): static
    {
        $this->categories()->detach();

        return $this->attachCategory($categories);
    }
	
	
	/**
	 * @param $categories
	 * @return bool
	 */
    public function hasCategory($categories): bool
    {
        if (is_string($categories)) {
            return $this->categories->contains('name', $categories);
        }

        if ($categories instanceof Category) {
            return $this->categories->contains('id', $categories->id);
        }

        if (is_array($categories)) {
            foreach ($categories as $category) {
                if ($this->hasCategory($category)) {
                    return true;
                }
            }

            return false;
        }

        return $categories->intersect($this->categories)->isNotEmpty();
    }
	
	/**
	 * @param $categories
	 * @return bool
	 */
    public function hasAnyCategory($categories): bool
    {
        return $this->hasCategory($categories);
    }
	
	/**
	 * @param $categories
	 * @return bool
	 */
    public function hasAllCategories($categories): bool
    {
        if (is_string($categories)) {
            return $this->categories->contains('name', $categories);
        }

        if ($categories instanceof Category) {
            return $this->categories->contains('id', $categories->id);
        }

        $categories = collect()->make($categories)->map(function ($category) {
            return $category instanceof Category ? $category->name : $category;
        });

        return $categories->intersect($this->categories->pluck('name')) === $categories;
    }
	
	
	/**
	 * @param $category
	 * @return Category
	 */
    protected function getStoredCategory($category): Category
    {
        if (is_numeric($category)) {
            return app(Category::class)->findById($category);
        }

        if (is_string($category)) {
            return app(Category::class)->findByName($category);
        }

        return $category;
    }
}
