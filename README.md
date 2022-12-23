

Laravel Categorizable Package
============

This Package is an implementation of a Nested-set hierarchy structure, which enables you to categorize your Eloquent models in a polymorphic way. just use the trait in the model, and you're good to go.
There is also a `Category` model which you can use directly or extend it in your model of choosing.


## Requirements
- PHP 8+
- Laravel 8+

## Installation

	composer require alibayat/laravel-categorizable

#### Publish and Run the migrations


```bash
php artisan vendor:publish --provider="AliBayat\LaravelCategorizable\CategorizableServiceProvider"

php artisan migrate
```


Laravel Categorizable package will be auto-discovered by Laravel. and if not: register the package in config/app.php providers array manually.
```php
'providers' => [
    ...
    \AliBayat\LaravelCategorizable\CategorizableServiceProvider::class,
],
```


#### Setup models - just use the Trait in the Model.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use AliBayat\LaravelCategorizable\Categorizable;

class Post extends Model
{
    use Categorizable;
}

```

## Usage
first we need to create some categories to work with. this package relies on another package called [laravel-nestedset](https://github.com/lazychaser/laravel-nestedset) that is responsible for creating, updating, removing and retrieving single or nested structured categories.
Here I demonstrate how to create categories and assign one as the other's child. but you can always check out the tests or refer to package's repository for full documentation.
https://github.com/lazychaser/laravel-nestedset


```php
use App\Models\Post;
use AliBayat\LaravelCategorizable\Category;

// first we create a bunch of categories

// create categories
$backEnd = Category::create(['name' => 'Back End']);
$frontEnd = Category::create(['name' => 'Front End']);
$php = Category::create(['name' => 'PHP']);

// assign "PHP" as a child of "Back End" category
$backEnd->appendNode($php);

//  assuming that we have a post instance
$post = Post::first();
```

### Multiple category structure

there are times that you may wish to have different category structures for different models. in that's the case you can also pass in a `type` parameter while creating a category. by default, type is set to `default`. while having a type you can also leverage Eloquent model scopes to filter categories with ease.      

### Create a Tree while creating new categories

it's also possible to pass a nested structure as the `children` property to the create method:
```php
$categoryWithChildAndGrandchild = Category::create([
    'name' => 'Foo',
    'children' => [
        [
            'name' => 'Bar',
            'children' => [
                [ 'name' => 'Baz' ],
            ],
        ],
    ],
]);
```

### Attach the post to category

```php
    $post->attachCategory($php);
```

### Detach the post from a category

```php
    $post->detachCategory($php); 
```

### Attach the post to list of categories

```php
    $post->syncCategories([
	    $php,
	    $backEnd
    ]); 
```

### Detach the post from all categories

```php
    $post->syncCategories([]); 
```

### Sync the categories attached to a post

```php
    $post->syncCategories([$frontEnd]);
```


### Check if post is attached to given categories (boolean)
```php
    // single use case
    $post->hasCategory($php);

    // multiple use case
    $post->hasCategory([
	    $php,
	    $backEnd
    ]);
```

### List of categories attached to the post (array [1 => 'BackEnd'])
```php
    $post->categoriesList();
```

### List of categories IDs attached to the post (array [1, 2, 3])
```php
    $post->categoriesIds();
```

### Get all posts attached to given category (MorphToMany)
```php
    $categoryPosts = Category::find(1)->entries(Post::class);
```

### Get all posts attached to given category and it's children (Builder)
```php
    $categoryAndDescendantsPosts = Category::find(1)->allEntries(Post::class);
```

---

## Methods
On the Base `Category` Model (or any other model that extends this class), you'll have access to various methods: 

```php
$result = Category::ancestorsOf($id);
$result = Category::ancestorsAndSelf($id);
$result = Category::descendantsOf($id);
$result = Category::descendantsAndSelf($id);
$result = Category::whereDescendantOf($node)->get();
$result = Category::whereNotDescendantOf($node)->get();
$result = Category::orWhereDescendantOf($node)->get();
$result = Category::orWhereNotDescendantOf($node)->get();
$result = Category::whereDescendantAndSelf($id)->get();
$result = Category::whereDescendantOrSelf($node)->get();
$result = Category::whereAncestorOf($node)->get();
$result = Category::whereAncestorOrSelf($id)->get();

$siblings = Category::find($id)->getSiblings();
$nextSibling = Category::find($id)->getNextSibling();
$nextSiblings = Category::find($id)->getNextSiblings();
$prevSibling = Category::find($id)->getPrevSibling();
$prevSiblings = Category::find($id)->getPrevSiblings();

$withDepth = Category::withDepth()->find($id);
$withSpecificDepth = Category::withDepth()->having('depth', '=', 1)->get();

$tree = Category::get()->toTree();
$flatTree = Category::get()->toFlatTree();

$bool = Category::isBroken();
$data = Category::countErrors();
Category::fixTree();
```
full documentation for these methods is available at `laravel-nestedset` package's readme. 

---

## Relationships

### categories() Relationship
```php
    $postWithCategories = Post::with('categories')->get();
```

### parent Relationship
```php
    $categoryWithParent = Category::with('parent')->find(1);
```

### children Relationship
```php
    $categoryWithChildren = Category::with('children')->find(1);
```

### ancestors Relationship
```php
    $categoryWithAncestors = Category::with('ancestors')->find(1);
```

### descendants Relationship
```php
    $categoryWithDescendants = Category::with('descendants')->find(1);
```



## Tests
this package comes with unit and feature tests as well (a total of 47 tests, 169 assertions) to ensure the provided features work as they should, you can run tests by the following composer command:
```
    composer test
```

#### Credits

 - Ali Bayat - <ali.bayat@live.com>
 - Thanks to all contributors
