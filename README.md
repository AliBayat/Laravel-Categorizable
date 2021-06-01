

Laravel Categorizable Package
============

This Package enables you to Categorize your Eloquent Models. just use the trait in the model and you're good to go.


### Requirements
- PHP 7.2+
- Laravel 7+

#### Composer Install

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

namespace App;

use Illuminate\Database\Eloquent\Model;
use AliBayat\LaravelCategorizable\Categorizable;

class Post extends Model
{
	use Categorizable;

}

```

### Usage
first of all we need to create some Category to work with. Laravel Categorizable package relies on another package called [laravel-nestedset](https://github.com/lazychaser/laravel-nestedset) that is responsible for creating, updating, removing and retrieving single or nested categories.
Here i demonstrate how to create categories and assign one as the other's child.. but you can always refer to package's repository for full documentation.
https://github.com/lazychaser/laravel-nestedset


```php
use App\Post;
use AliBayat\LaravelCategorizable\Category;

// first we create a bunch of categories

// create "BackEnd" category
Category::create([
	'name' => 'BackEnd'
]);

// create "PHP" category
Category::create([
	'name' => 'PHP'
]);

// create "FrontEnd" category
Category::create([
	'name' => 'FrontEnd'
]);

// create "Test" Category (alternative way)
$test = new Category();
$test->name = 'Test';
$test->save();


// assign "PHP" as a child of "BackEnd" category
$parent = Category::findByName('BackEnd');
$child = Category::findByName('PHP');
$parent->appendNode($child);

// delete "Test" Category
$testObj = Category::findByName('Test');
$testObj->delete();



//  assuming that we have these variables
$post = Post::first();

// 3 different ways of getting a category's instance
$backendCategory = Category::findById(1);	// 'BackEnd'
$phpCategory = Category::findByName('PHP');	// 'PHP'
$frontendCategory = Category::find(3);		// 'FrontEnd'


```

### Attach the post to category

```php
    $post->attachCategory($phpCategory);
```

### Detach the post from a category

```php
    $post->detachCategory($phpCategory); 
```

### Attach the post to list of categories

```php
    $post->syncCategories([
	    $phpCategory,
	    $backendCategory
	    ]); 
```

### Detach the post from all categories

```php
    $post->syncCategories([]); 
```

### Sync the categories attached to a post

```php
    $post->syncCategories([
	    $frontendCategory
	    ]); 


    // removes attached categories & adds the given categories
```


### Check if post is attached to categories (boolean)
```php
    // single use case
    $post->hasCategory($phpCategory);

    // multiple use case
    $post->hasCategory([
	    $phpCategory,
	    $backendCategory
	    ]);


    // return boolean
```

### List of categories attached to the post (array)
```php
    $post->categoriesList();


    // return array [id => name]
```

### List of categories IDs attached to the post (array)
```php
    $post->categoriesId();


    // return array
```

### Get all posts attached to given category (collection)
```php
    $categoryPosts = Category::find(1)
	    ->entries(Post::class)
	    ->get();


    // return collection
```

---

## Relationships

### categories() Relationship
```php
    $postWithCategories = Post::with('categories')
	    ->get();


     // you have access to categories() relationship in case you need eager loading
    
```

### parent Relationship
```php
    $comment = Post::first()->comments()->first();
    
    $comment->parent;
    // return the comment's parent if available

```

### children Relationship
```php
    $comment = Post::first()->comments()->first();
    
    $comment->children;
    // return the comment's children if any

```

### ancestors Relationship
```php
    $comment = Post::first()->comments()->first();
    
    $comment->ancestors;
    // return the comment's ancestors if any

```

#### Credits

 - Ali Bayat - <ali.bayat@live.com>
