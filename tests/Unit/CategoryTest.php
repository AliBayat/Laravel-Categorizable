<?php

declare(strict_types=1);

namespace AliBayat\LaravelCategorizable\Tests\Unit;

use AliBayat\LaravelCategorizable\{Category, Tests\TestCase};
use Exception;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\{Collection, Str};
use Kalnoy\Nestedset\{AncestorsRelation, DescendantsRelation};

class CategoryTest extends TestCase
{
	use WithFaker;
	
	/**
	 * @var Category
	 */
	private Category $level1Category;
	
	/**
	 * @var Category
	 */
	private Category $level2Category;
	
	/**
	 * @var Category
	 */
	private Category $level3Category;
	
	/**
	 * @return void
	 */
	public function setUp(): void
	{
		parent::setUp();
		$this->level1Category = Category::create(['name' => $this->faker->title])->makeRoot();
		$this->level2Category = Category::create(['name' => $this->faker->title]);
		$this->level3Category = Category::create(['name' => $this->faker->title]);
		$this->level1Category->appendNode($this->level2Category);
		$this->level2Category->appendNode($this->level3Category);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_generates_a_slug_on_the_fly(): void
	{
		$title = $this->faker->words(2, true);
		$slug = Str::slug($title);
		$category = Category::create(['name' => $title]);
		
		$this->assertDatabaseHas($this->mainTable, ['name' => $title, 'slug' => $slug]);
		$this->assertEquals($slug, $category->fresh()->slug);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_generates_a_slug_for_persian_characters(): void
	{
		$title = "جامعه فارسی زبانان";
		$slug = str_replace(' ', '-', $title);
		$category = Category::create(['name' => $title]);
		$this->assertDatabaseHas($this->mainTable, ['name' => $title, 'slug' => $slug]);
		$this->assertEquals($slug, $category->fresh()->slug);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_parent_relationship(): void
	{
		$this->assertInstanceOf(Category::class, $this->level2Category->parent);
		$this->assertInstanceOf(BelongsTo::class, $this->level2Category->parent());
		$this->assertEquals($this->level2Category->parent->id, $this->level1Category->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_children_relationship(): void
	{
		$this->assertInstanceOf(Collection::class, $this->level2Category->children);
		$this->assertInstanceOf(HasMany::class, $this->level2Category->children());
		$this->assertEquals($this->level2Category->children()->first()->id, $this->level3Category->id);
		$this->assertCount(1, $this->level2Category->fresh()->children);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_an_ancestors_relationship(): void
	{
		$this->assertInstanceOf(Collection::class, $this->level3Category->ancestors);
		$this->assertInstanceOf(AncestorsRelation::class, $this->level3Category->ancestors());
		$this->assertEquals($this->level3Category->ancestors()->first()->id, $this->level1Category->id);
		$this->assertCount(2, $this->level3Category->fresh()->ancestors);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_descendants_relationship(): void
	{
		$this->assertInstanceOf(Collection::class, $this->level1Category->descendants);
		$this->assertInstanceOf(DescendantsRelation::class, $this->level1Category->descendants());
		$this->assertEquals($this->level1Category->descendants()->first()->id, $this->level2Category->id);
		$this->assertCount(2, $this->level1Category->fresh()->descendants);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_ancestors_of_method(): void
	{
		$ancestorsOf = Category::ancestorsOf($this->level3Category->id);
		$this->assertInstanceOf(Collection::class, $ancestorsOf);
		$this->assertCount(2, $ancestorsOf);
		$this->assertEquals($this->level1Category->id, $ancestorsOf->first()->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_ancestors_and_self_method(): void
	{
		$ancestorsAndSelf = Category::ancestorsAndSelf($this->level3Category->id);
		$this->assertInstanceOf(Collection::class, $ancestorsAndSelf);
		$this->assertCount(3, $ancestorsAndSelf);
		$this->assertEquals($this->level1Category->id, $ancestorsAndSelf->first()->id);
		
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_descendants_of_method(): void
	{
		$descendantsOf = Category::descendantsOf($this->level1Category->id);
		$this->assertInstanceOf(Collection::class, $descendantsOf);
		$this->assertCount(2, $descendantsOf);
		$this->assertEquals($this->level2Category->id, $descendantsOf->first()->id);
		
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_descendants_and_self_method(): void
	{
		$descendantsAndSelf = Category::descendantsAndSelf($this->level1Category->id);
		$this->assertInstanceOf(Collection::class, $descendantsAndSelf);
		$this->assertCount(3, $descendantsAndSelf);
		$this->assertEquals($this->level1Category->id, $descendantsAndSelf->first()->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_reversed_method(): void
	{
		$result = Category::reversed()->get();
		$this->assertEquals($this->level3Category->id, $result[0]->id);
		$this->assertEquals($this->level2Category->id, $result[1]->id);
		$this->assertEquals($this->level1Category->id, $result[2]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_to_tree_method(): void
	{
		$tree = Category::get()->toTree();
		$this->assertEquals($this->level1Category->id, $tree[0]['id']);
		$this->assertEquals($this->level2Category->id, $tree[0]['children'][0]['id']);
		$this->assertEquals($this->level3Category->id, $tree[0]['children'][0]['children'][0]['id']);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_to_flat_tree_method(): void
	{
		$flatTree = Category::get()->toFlatTree();
		$this->assertEquals($this->level1Category->id, $flatTree[0]['id']);
		$this->assertEquals($this->level2Category->id, $flatTree[1]['id']);
		$this->assertEquals($this->level3Category->id, $flatTree[2]['id']);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_knows_about_its_depth(): void
	{
		$this->assertEquals(0, Category::withDepth()->find($this->level1Category->id)->depth);
		$this->assertEquals(1, Category::withDepth()->find($this->level2Category->id)->depth);
		$this->assertEquals(2, Category::withDepth()->find($this->level3Category->id)->depth);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_knows_about_its_siblings(): void
	{
		$this->assertCount(0, $this->level3Category->getSiblings());
		
		$this->level2Category->appendNode(
			$sibling1 = Category::create(['name' => $this->faker->title])
		);
		$this->assertCount(1, $this->level3Category->getSiblings());
		
		$this->level2Category->appendNode(
			$sibling2 = Category::create(['name' => $this->faker->title])
		);
		$this->assertCount(2, $this->level3Category->getSiblings());
		
		$this->assertEquals($sibling1->id, $this->level3Category->getNextSibling()->id);
		$this->assertEquals($sibling2->id, $sibling1->getNextSibling()->id);
		$this->assertCount(2, $this->level3Category->getNextSiblings());
		
		$this->assertEquals($sibling1->id, $sibling2->getPrevSibling()->id);
		$this->assertEquals($this->level3Category->id, $sibling1->getPrevSibling()->id);
		$this->assertCount(2, $sibling2->getPrevSiblings());
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_is_root_constraint(): void
	{
		$root = Category::whereIsRoot()->get();
		$this->assertCount(1, $root);
		$this->assertEquals($this->level1Category->id, $root[0]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_has_parent_constraint(): void
	{
		$withParents = Category::hasParent()->get();
		$this->assertCount(2, $withParents);
		$this->assertEquals($this->level2Category->id, $withParents[0]->id);
		$this->assertEquals($this->level3Category->id, $withParents[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_is_leaf_constraint(): void
	{
		$leafs = Category::whereIsLeaf()->get();
		$this->assertCount(1, $leafs);
		$this->assertEquals($this->level3Category->id, $leafs[0]->id);
		
		$this->level2Category->appendNode(
			$otherLeaf = Category::create(['name' => $this->faker->title])
		);
		
		$leafs = Category::whereIsLeaf()->get();
		$this->assertCount(2, $leafs);
		$this->assertEquals($this->level3Category->id, $leafs[0]->id);
		$this->assertEquals($otherLeaf->id, $leafs[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_has_children_constraint(): void
	{
		$withChildren = Category::hasChildren()->get();
		$this->assertCount(2, $withChildren);
		$this->assertEquals($this->level1Category->id, $withChildren[0]->id);
		$this->assertEquals($this->level2Category->id, $withChildren[1]->id);
		
		$this->level3Category->appendNode(
			Category::create(['name' => $this->faker->title])
		);
		
		$withChildren = Category::hasChildren()->get();
		$this->assertCount(3, $withChildren);
		$this->assertEquals($this->level1Category->id, $withChildren[0]->id);
		$this->assertEquals($this->level2Category->id, $withChildren[1]->id);
		$this->assertEquals($this->level3Category->id, $withChildren[2]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_is_after_constraint(): void
	{
		$nodesAfter = Category::whereIsAfter($this->level1Category->id)->get();
		$this->assertCount(2, $nodesAfter);
		$this->assertEquals($this->level2Category->id, $nodesAfter[0]->id);
		$this->assertEquals($this->level3Category->id, $nodesAfter[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_is_before_constraint(): void
	{
		$nodesBefore = Category::whereIsBefore($this->level3Category->id)->get();
		$this->assertCount(2, $nodesBefore);
		$this->assertEquals($this->level1Category->id, $nodesBefore[0]->id);
		$this->assertEquals($this->level2Category->id, $nodesBefore[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_descendant_of_constraint(): void
	{
		$descendantOf = Category::whereDescendantOf($this->level1Category)->get();
		$this->assertCount(2, $descendantOf);
		$this->assertEquals($this->level2Category->id, $descendantOf[0]->id);
		$this->assertEquals($this->level3Category->id, $descendantOf[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_not_descendant_of_constraint(): void
	{
		$notDescendantOf = Category::whereNotDescendantOf($this->level1Category)->get();
		$this->assertCount(1, $notDescendantOf);
		$this->assertEquals($this->level1Category->id, $notDescendantOf[0]->id);
		
		$notDescendantOf = Category::whereNotDescendantOf($this->level3Category)->get();
		$this->assertCount(3, $notDescendantOf);
		$this->assertEquals($this->level1Category->id, $notDescendantOf[0]->id);
		$this->assertEquals($this->level2Category->id, $notDescendantOf[1]->id);
		$this->assertEquals($this->level3Category->id, $notDescendantOf[2]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_or_where_descendant_of_constraint(): void
	{
		$orDescendantOf = Category::orWhereDescendantOf($this->level1Category)->get();
		$this->assertCount(2, $orDescendantOf);
		$this->assertEquals($this->level2Category->id, $orDescendantOf[0]->id);
		$this->assertEquals($this->level3Category->id, $orDescendantOf[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_or_where_not_descendant_of_constraint(): void
	{
		$orNotDescendantOf = Category::orWhereNotDescendantOf($this->level2Category)->get();
		$this->assertCount(2, $orNotDescendantOf);
		$this->assertEquals($this->level1Category->id, $orNotDescendantOf[0]->id);
		$this->assertEquals($this->level2Category->id, $orNotDescendantOf[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_descendant_or_self_constraint(): void
	{
		$descendantOrSelf = Category::whereDescendantOrSelf($this->level1Category->id)->get();
		$this->assertCount(3, $descendantOrSelf);
		$this->assertEquals($this->level1Category->id, $descendantOrSelf[0]->id);
		$this->assertEquals($this->level2Category->id, $descendantOrSelf[1]->id);
		$this->assertEquals($this->level3Category->id, $descendantOrSelf[2]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_ancestor_of_constraint(): void
	{
		$ancestorOf = Category::whereAncestorOf($this->level3Category->id)->get();
		$this->assertCount(2, $ancestorOf);
		$this->assertEquals($this->level1Category->id, $ancestorOf[0]->id);
		$this->assertEquals($this->level2Category->id, $ancestorOf[1]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_where_ancestor_or_self_constraint(): void
	{
		$ancestorOrSelf = Category::whereAncestorOrSelf($this->level3Category->id)->get();
		$this->assertCount(3, $ancestorOrSelf);
		$this->assertEquals($this->level1Category->id, $ancestorOrSelf[0]->id);
		$this->assertEquals($this->level2Category->id, $ancestorOrSelf[1]->id);
		$this->assertEquals($this->level3Category->id, $ancestorOrSelf[2]->id);
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_is_root_check(): void
	{
		$this->assertTrue($this->level1Category->isRoot());
		$this->assertFalse($this->level2Category->isRoot());
		$this->assertFalse($this->level3Category->isRoot());
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_is_child_of_check(): void
	{
		$this->assertTrue($this->level3Category->isChildOf($this->level2Category));
		$this->assertTrue($this->level2Category->isChildOf($this->level1Category));
		
		$this->assertFalse($this->level1Category->isChildOf($this->level2Category));
		$this->assertFalse($this->level2Category->isChildOf($this->level3Category));
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_ancestor_of_check(): void
	{
		$this->assertTrue($this->level1Category->isAncestorOf($this->level2Category));
		$this->assertTrue($this->level2Category->isAncestorOf($this->level3Category));
		$this->assertTrue($this->level1Category->isAncestorOf($this->level3Category));
		
		$this->assertFalse($this->level3Category->isAncestorOf($this->level2Category));
		$this->assertFalse($this->level2Category->isAncestorOf($this->level1Category));
		$this->assertFalse($this->level3Category->isAncestorOf($this->level1Category));
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_is_sibling_of_check(): void
	{
		$this->level2Category->appendNode(
			$sibling = Category::create(['name' => $this->faker->title])
		);
		
		$this->assertTrue($this->level3Category->isSiblingOf($sibling));
		$this->assertTrue($sibling->isSiblingOf($this->level3Category));
		$this->assertFalse($this->level2Category->isSiblingOf($this->level3Category));
		$this->assertFalse($this->level1Category->isSiblingOf($this->level3Category));
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function it_has_a_is_leaf_check(): void
	{
		$this->assertFalse($this->level1Category->isLeaf());
		$this->assertFalse($this->level2Category->isLeaf());
		$this->assertTrue($this->level3Category->isLeaf());
	}
	
	/**
	 * @test
	 * @return void
	 */
	public function deleting_nodes_will_remove_any_related_descendants(): void
	{
		$this->level2Category->delete();
		$this->assertNull($this->level2Category->fresh());
		$this->assertNull($this->level3Category->fresh());
		$this->assertDatabaseCount($this->mainTable, 1);
		$this->assertCount(0, Category::whereDescendantOf($this->level1Category)->get());
	}
	
	/**
	 * @test
	 * @return void
	 * @throws Exception
	 */
	public function it_knows_if_the_tree_structure_is_broken(): void
	{
		$this->assertFalse(Category::isBroken());
		Category::where('id', $this->level2Category->id)->update(['parent_id' => random_int(50, 60)]);
		$this->assertTrue(Category::isBroken());
	}
	
	/**
	 * @test
	 * @return void
	 * @throws Exception
	 */
	public function it_knows_about_the_structural_errors(): void
	{
		Category::where('id', $this->level2Category->id)->update(['parent_id' => random_int(50, 60)]);
		$errors = Category::countErrors();
		$this->assertIsArray($errors);
		$this->assertArrayHasKey('oddness', $errors);
		$this->assertArrayHasKey('duplicates', $errors);
		$this->assertArrayHasKey('wrong_parent', $errors);
		$this->assertArrayHasKey('missing_parent', $errors);
	}
	
	/**
	 * @test
	 * @return void
	 * @throws Exception
	 */
	public function it_knows_how_to_fix_the_structural_errors(): void
	{
		Category::where('id', $this->level2Category->id)->update(['parent_id' => random_int(50, 60)]);
		$this->assertTrue(Category::isBroken());
		Category::fixTree();
		$this->assertFalse(Category::isBroken());
		$errors = Category::countErrors();
		$this->assertEquals(0, $errors['wrong_parent']);
	}
}