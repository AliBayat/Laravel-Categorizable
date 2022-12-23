<?php

declare(strict_types=1);

namespace AliBayat\LaravelCategorizable\Tests\Feature;

use AliBayat\LaravelCategorizable\{Categorizable, Category, Tests\Post, Tests\TestCase};
use Illuminate\Foundation\Testing\WithFaker;

class CategorizableTest extends TestCase
{
	use WithFaker;
	
	/**
	 * @var Post
	 */
	private Post $post;
	
	/**
	 * @var Category
	 */
	private Category $category;
	
	/**
	 * @return void
	 */
	public function setUp(): void
	{
		parent::setUp();
		$this->post = Post::create(['title' => $this->faker->title, 'body' => $this->faker->paragraph]);
		$this->category = Category::create(['name' => $this->faker->title]);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Categorizable::attachCategory()
	 */
	public function models_can_be_attached_to_categories(): void
	{
		$this->post->attachCategory($this->category);
		
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post)
		]);
		$this->assertDatabaseCount($this->morphTable, 1);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Categorizable::detachCategory()
	 */
	public function models_can_be_detached_from_categories(): void
	{
		$this->post->attachCategory($this->category);
		$this->post->detachCategory($this->category);
		
		$this->assertDatabaseMissing($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post)
		]);
		$this->assertDatabaseCount($this->morphTable, 0);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Categorizable::syncCategories()
	 */
	public function all_categories_of_a_model_can_be_synchronized(): void
	{
		// single
		$otherCategory = Category::create(['name' => $this->faker->title]);
		$this->post->attachCategory($this->category);
		$this->post->syncCategories($otherCategory);
		
		$this->assertDatabaseMissing($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $this->category->id
		]);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $otherCategory->id
		]);
		$this->assertDatabaseCount($this->morphTable, 1);
		
		// multiple
		$this->post->syncCategories([$this->category, $otherCategory]);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $this->category->id
		]);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $otherCategory->id
		]);
		$this->assertDatabaseCount($this->morphTable, 2);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Categorizable::syncCategories()
	 */
	public function all_categories_of_a_model_can_be_deleted(): void
	{
		$otherCategory = Category::create(['name' => $this->faker->title]);
		$this->post->attachCategory($this->category);
		$this->post->attachCategory($otherCategory);
		
		$this->assertDatabaseCount($this->morphTable, 2);
		
		$this->post->syncCategories([]);
		
		$this->assertDatabaseMissing($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $this->category->id
		]);
		$this->assertDatabaseMissing($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $otherCategory->id
		]);
		$this->assertDatabaseCount($this->morphTable, 0);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Categorizable::hasCategory()
	 */
	public function a_model_knows_if_its_attached_to_a_given_category(): void
	{
		$this->post->attachCategory($this->category);
		
		$this->assertTrue($this->post->hasCategory($this->category));
	}
	
	/**
	 * @test
	 * @return void
	 * @see Categorizable::hasCategory()
	 */
	public function a_model_knows_if_its_attached_to_the_given_categories(): void
	{
		$otherCategory = Category::create(['name' => $this->faker->title]);
		$this->post->attachCategory($this->category);
		$this->post->attachCategory($otherCategory);
		
		$this->assertTrue($this->post->hasCategory([$this->category, $otherCategory]));
	}
	
	/**
	 * @test
	 * @return void
	 * @see Categorizable::categoriesList()
	 */
	public function a_model_knows_about_the_list_of_its_categories(): void
	{
		$otherCategory = Category::create(['name' => $this->faker->title]);
		$this->post->attachCategory($this->category);
		$this->post->attachCategory($otherCategory);
		
		$list = $this->post->categoriesList();
		
		$this->assertIsArray($list);
		$this->assertCount(2, $list);
		$this->assertArrayHasKey($this->category->id, $list);
		$this->assertArrayHasKey($otherCategory->id, $list);
		$this->assertContains($this->category->name, $list);
		$this->assertContains($otherCategory->name, $list);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Category::entries()
	 */
	public function a_category_knows_about_the_list_of_models_attached_to_it(): void
	{
		$otherPost = Post::create(['title' => $this->faker->title, 'body' => $this->faker->paragraph]);
		$this->post->attachCategory($this->category);
		$otherPost->attachCategory($this->category);
		
		$entries = Category::find($this->category->id)->entries(Post::class);
		$entriesArray = $entries->get()->toArray();

		$this->assertCount(2, $entriesArray);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $this->category->id
		]);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $otherPost->id,
			'model_type' => get_class($otherPost),
			'category_id' => $this->category->id
		]);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Category::allEntries()
	 */
	public function a_category_knows_about_the_list_of_models_attached_to_it_and_its_children(): void
	{
		// root has 2 posts, level 1 has 1 post, and level 2 has 1 post.
		$otherPost = Post::create(['title' => $this->faker->title, 'body' => $this->faker->paragraph]);
		$this->post->attachCategory($this->category);
		$otherPost->attachCategory($this->category);
		$categoryLevel1 = Category::create(['name' => $this->faker->title]);
		$this->category->appendNode($categoryLevel1);
		$postLevel1 = Post::create(['title' => $this->faker->title, 'body' => $this->faker->paragraph]);
		$postLevel1->attachCategory($categoryLevel1);
		$categoryLevel2 = Category::create(['name' => $this->faker->title]);
		$categoryLevel1->appendNode($categoryLevel2);
		$postLevel2 = Post::create(['title' => $this->faker->title, 'body' => $this->faker->paragraph]);
		$postLevel2->attachCategory($categoryLevel2);
		
		$allEntries = Category::find($this->category->id)->allEntries(Post::class);
		$allEntriesArray = $allEntries->get()->toArray();

		$this->assertCount(4, $allEntriesArray);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $this->post->id,
			'model_type' => get_class($this->post),
			'category_id' => $this->category->id
		]);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $otherPost->id,
			'model_type' => get_class($otherPost),
			'category_id' => $this->category->id
		]);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $postLevel1->id,
			'model_type' => get_class($postLevel1),
			'category_id' => $categoryLevel1->id
		]);
		$this->assertDatabaseHas($this->morphTable, [
			'model_id' => $postLevel2->id,
			'model_type' => get_class($postLevel2),
			'category_id' => $categoryLevel2->id
		]);
	}
	
	/**
	 * @test
	 * @return void
	 * @see Category::tree()
	 */
	public function category_model_knows_about_the_hierarchy_nested_structure_tree(): void
	{
		$categoryLevel1 = Category::create(['name' => $this->faker->title]);
		$this->category->appendNode($categoryLevel1);
		$categoryLevel2 = Category::create(['name' => $this->faker->title]);
		$categoryLevel1->appendNode($categoryLevel2);
		$categoryLevel3 = Category::create(['name' => $this->faker->title]);
		$categoryLevel2->appendNode($categoryLevel3);
		
		$tree = Category::tree();
		
		$this->assertContains($this->category->name, array_column($tree, 'name'));
		$this->assertContains($categoryLevel1->name, array_column($tree[0]['children'], 'name'));
		$this->assertContains($categoryLevel2->name, array_column($tree[0]['children'][0]['children'], 'name'));
		$this->assertContains($categoryLevel3->name, array_column($tree[0]['children'][0]['children'][0]['children'], 'name'));
	}
}