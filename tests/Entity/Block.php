<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spyck\VisualizationBundle\Entity\Block;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Widget;

final class BlockTest extends TestCase
{
    private Block $block;

    protected function setUp(): void
    {
        parent::setUp();

        $this->block = new Block();
    }

    public function testDashboardProperty()
    {
        $dashboard = new Dashboard();

        $this->block->setDashboard($dashboard);
        $this->assertSame($dashboard, $this->block->getDashboard());
    }

    public function testWidgetProperty()
    {
        $widget = new Widget();

        $this->block->setWidget($widget);

        $this->assertSame($widget, $this->block->getWidget());
    }

    public function testNameProperty()
    {
        $this->block->setName('Test Block');

        $this->assertSame('Test Block', $this->block->getName());
    }

    public function testDescriptionProperty()
    {
        $this->block->setDescription('This is a test block');

        $this->assertSame('This is a test block', $this->block->getDescription());
    }

    public function testSizeProperty()
    {
        $this->block->setSize(Block::SIZE_LARGE);

        $this->assertSame(Block::SIZE_LARGE, $this->block->getSize());
    }

    public function testPositionProperty()
    {
        $this->block->setPosition(1);

        $this->assertSame(1, $this->block->getPosition());
    }

    public function testVariablesProperty()
    {
        $variables = ['var1' => 'value1', 'var2' => 'value2'];

        $this->block->setVariables($variables);

        $this->assertSame($variables, $this->block->getVariables());
    }

    public function testChartProperty()
    {
        $this->block->setChart('test-chart');

        $this->assertSame('test-chart', $this->block->getChart());
    }

    public function testFilterProperty()
    {
        $this->block->setFilter(true);

        $this->assertTrue($this->block->hasFilter());
    }

    public function testFilterViewProperty()
    {
        $this->block->setFilterView(false);

        $this->assertFalse($this->block->hasFilterView());
    }

    public function testActiveProperty()
    {
        $this->block->setActive(true);

        $this->assertTrue($this->block->isActive());
    }

    public function testGetSizes()
    {
        $sizeData = Block::getSizes(false);

        $this->assertArrayHasKey(Block::SIZE_LARGE, $sizeData);
        $this->assertArrayHasKey(Block::SIZE_MEDIUM, $sizeData);
        $this->assertArrayHasKey(Block::SIZE_SMALL, $sizeData);
    }

    public function testToString()
    {
        $dashboard = new Dashboard();
        $dashboard->setName('Test Dashboard');

        $this->block->setDashboard($dashboard)->setPosition(2);

        $this->assertSame('Test Dashboard at position 2', (string) $this->block);
    }
}
