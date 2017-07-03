<?php

namespace Code16\Sharp\Tests\Unit\EntitiesList;

use Code16\Sharp\EntitiesList\Containers\EntitiesListDataContainer;
use Code16\Sharp\EntitiesList\EntitiesListFilter;
use Code16\Sharp\EntitiesList\EntitiesListQueryParams;
use Code16\Sharp\EntitiesList\SharpEntitiesList;
use Code16\Sharp\Tests\SharpTestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class SharpEntitiesListTest extends SharpTestCase
{
    /** @test */
    function we_can_get_containers()
    {
        $list = new class extends SharpEntitiesListTestList {
            function buildListDataContainers()
            {
                $this->addDataContainer(
                    EntitiesListDataContainer::make("name")
                        ->setLabel("Name")
                );
            }
        };

        $this->assertEquals(["name" => [
            "key" => "name",
            "label" => "Name",
            "sortable" => false,
            "html" => true,
        ]], $list->dataContainers());
    }

    /** @test */
    function we_can_get_layout()
    {
        $list = new class extends SharpEntitiesListTestList {
            function buildListDataContainers()
            {
                $this->addDataContainer(
                    EntitiesListDataContainer::make("name")
                )->addDataContainer(
                    EntitiesListDataContainer::make("age")
                );
            }
            function buildListLayout()
            {
                $this->addColumn("name", 6, 12)
                    ->addColumnLarge("age", 6);
            }
        };

        $this->assertEquals([
            [
                "key" => "name", "size" => 6, "sizeXS" => 12, "hideOnXS" => false,
            ], [
                "key" => "age", "size" => 6, "sizeXS" => 6, "hideOnXS" => true,
            ]
        ], $list->listLayout());
    }

    /** @test */
    function we_can_get_list_data()
    {
        $form = new class extends SharpEntitiesListTestList {
            function getListData(EntitiesListQueryParams $params): array
            {
                return [
                    ["name" => "John Wayne", "age" => 22, "job" => "actor"],
                    ["name" => "Mary Wayne", "age" => 26, "job" => "truck driver"]
                ];
            }
            function buildListDataContainers()
            {
                $this->addDataContainer(
                    EntitiesListDataContainer::make("name")
                )->addDataContainer(
                    EntitiesListDataContainer::make("age")
                );
            }
        };

        $this->assertEquals([
            "items" => [
                ["name" => "John Wayne", "age" => 22],
                ["name" => "Mary Wayne", "age" => 26],
            ]
        ], $form->data(new EntitiesListQueryParams()));
    }

    /** @test */
    function we_can_get_paginated_list_data()
    {
        $form = new class extends SharpEntitiesListTestList {
            function getListData(EntitiesListQueryParams $params)
            {
                $data = [
                    ["name" => "John Wayne", "age" => 22, "job" => "actor"],
                    ["name" => "Mary Wayne", "age" => 26, "job" => "truck driver"]
                ];

                return new LengthAwarePaginator($data, 10, 2, 1);
            }
            function buildListDataContainers()
            {
                $this->addDataContainer(
                    EntitiesListDataContainer::make("name")
                )->addDataContainer(
                    EntitiesListDataContainer::make("age")
                );
            }
        };

        $this->assertEquals([
            "items" => [
                ["name" => "John Wayne", "age" => 22],
                ["name" => "Mary Wayne", "age" => 26],
            ], "page" => 1, "pageSize" => 2, "totalCount" => 10
        ], $form->data(new EntitiesListQueryParams()));
    }

    /** @test */
    function we_can_get_list_config()
    {
        $list = new class extends SharpEntitiesListTestList {
            function buildListConfig()
            {
                $this->setSearchable()
                    ->setPaginated();
            }
        };

        $this->assertEquals([
            "searchable" => true,
            "paginated" => true,
            "instanceIdAttribute" => "id",
            "displayMode" => "list",
            "defaultSort" => null,
            "defaultSortDir" => null
        ], $list->listConfig());
    }

    /** @test */
    function we_can_get_list_filters_config()
    {
        $list = new class extends SharpEntitiesListTestList {
            function buildListConfig()
            {
                $this->addFilter("test", new class implements EntitiesListFilter {
                    public function values() { return [1 => "A", 2 => "B"]; }
                    public function multiple() { return false; }
                });
            }
        };

        $this->assertArraySubset([
            "filter_test" => [
                "multiple" => false,
                "values" => [1 => "A", 2 => "B"]
            ]
        ], $list->listConfig());
    }
}

abstract class SharpEntitiesListTestList extends SharpEntitiesList
{
    function buildListDataContainers() {}
    function buildListLayout() {}
    function buildListConfig() {}
    function getListData(EntitiesListQueryParams $params) { return []; }
}