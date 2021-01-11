<?php

declare(strict_types=1);

namespace byrokrat\accounting\Sie4\Parser;

/**
 * @covers \byrokrat\accounting\Sie4\Parser\DimensionBuilder
 */
class DimensionBuilderTest extends \PHPUnit\Framework\TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    public function testCreateAndGetDimension()
    {
        $dimensionBuilder = new DimensionBuilder(
            $this->createMock(Logger::class)
        );

        $dimensionBuilder->addDimension('1', 'foobar');

        $this->assertSame(
            $dimensionBuilder->getDimension('1'),
            $dimensionBuilder->getDimension('1')
        );

        $this->assertCount(1, $dimensionBuilder->getDimensions());
    }

    public function testCreateAndGetChildDimension()
    {
        $dimensionBuilder = new DimensionBuilder(
            $this->createMock(Logger::class)
        );

        $dimensionBuilder->addDimension('parent', '');
        $dimensionBuilder->addDimension('child', '', 'parent');

        $this->assertSame(
            [$dimensionBuilder->getDimension('child')],
            $dimensionBuilder->getDimension('parent')->getChildren()
        );
    }

    public function testCreateAndGetObject()
    {
        $dimensionBuilder = new DimensionBuilder(
            $this->createMock(Logger::class)
        );

        $dimensionBuilder->addDimension('parent', '');
        $dimensionBuilder->addObject('parent', 'object', '');

        $this->assertSame(
            [$dimensionBuilder->getObject('parent', 'object')],
            $dimensionBuilder->getDimension('parent')->getChildren()
        );

        $this->assertCount(2, $dimensionBuilder->getDimensions());
    }

    public function testGetUnspecifiedObject()
    {
        $logger = $this->prophesize(Logger::class);

        $dimensionBuilder = new DimensionBuilder(
            $logger->reveal()
        );

        $this->assertSame(
            'UNSPECIFIED',
            $dimensionBuilder->getObject('1', '2')->getDescription()
        );

        $logger->log('warning', 'Object number 1.2 not defined', 1)->shouldHaveBeenCalled();
    }

    public function testGetUnspecifiedDimension()
    {
        $logger = $this->prophesize(Logger::class);

        $dimensionBuilder = new DimensionBuilder(
            $logger->reveal()
        );

        $this->assertSame(
            'UNSPECIFIED',
            $dimensionBuilder->getDimension('100')->getDescription()
        );

        $logger->log('warning', 'Dimension number 100 not defined', 1)->shouldHaveBeenCalled();
    }

    public function testGetUnspecifiedReservedDimension()
    {
        $dimensionBuilder = new DimensionBuilder(
            $this->createMock(Logger::class)
        );

        $this->assertSame(
            'Anställd',
            $dimensionBuilder->getDimension('7')->getDescription()
        );
    }

    public function testGetUnspecifiedReservedCostDimension()
    {
        $dimensionBuilder = new DimensionBuilder(
            $this->createMock(Logger::class)
        );

        $dim = $dimensionBuilder->getDimension('2');

        $this->assertSame(
            'Kostnadsbärare',
            $dim->getDescription()
        );

        $this->assertSame(
            [$dim],
            $dimensionBuilder->getDimension('1')->getChildren(),
        );
    }
}
