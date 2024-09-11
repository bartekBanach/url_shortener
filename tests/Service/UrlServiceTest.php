<?php
/**
 * Tests for UrlService.
 */

namespace App\Tests\Service;

use App\Entity\Url;
use App\Repository\UrlRepository;
use App\Service\TagServiceInterface;
use App\Service\UrlService;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * UrlServiceTest class.
 */
class UrlServiceTest extends TestCase
{
    private UrlRepository|MockObject $urlRepository;
    private TagServiceInterface|MockObject $tagService;
    private PaginatorInterface|MockObject $paginator;
    private UrlService $urlService;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->urlRepository = $this->createMock(UrlRepository::class);
        $this->tagService = $this->createMock(TagServiceInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->urlService = new UrlService(
            $this->urlRepository,
            $this->tagService,
            $this->paginator
        );
    }

    /**
     * Test generateShortUrlCode method.
     */
    public function testGenerateShortUrlCode(): void
    {
        // given
        $longUrl = 'https://example.com/some-long-url';
        $this->urlRepository
            ->expects($this->once())
            ->method('findOneByShortUrl')
            ->with($this->isType('string'))
            ->willReturn(null);

        // when
        $shortCode = $this->urlService->generateShortUrlCode($longUrl);

        // then
        $this->assertSame(7, strlen($shortCode), 'Short URL code should be exactly 7 characters long');
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{7}$/', $shortCode, 'Short URL code should only contain alphanumeric characters');
    }

    /**
     * Test generateShortUrlCode main code generation is deterministic.
     */
    public function testGenerateShortUrlCodeDeterministic(): void
    {
        $longUrl = 'https://example.com/some-long-url';

        $this->urlRepository
            ->expects($this->exactly(2))
            ->method('findOneByShortUrl')
            ->with($this->isType('string'))
            ->willReturn(null);

        $shortCode1 = $this->urlService->generateShortUrlCode($longUrl);
        $shortCode2 = $this->urlService->generateShortUrlCode($longUrl);

        $this->assertSame($shortCode1, $shortCode2, 'Short URL code should be the same for the same long URL');

        $this->assertSame(7, strlen($shortCode1), 'Short URL code should be exactly 7 characters long');
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{7}$/', $shortCode1, 'Short URL code should only contain alphanumeric characters');
    }

    /**
     * Test generateShortUrlCode method when the short code already exists.
     */
    public function testGenerateShortUrlCodeGeneratesNewCodeWhenDuplicate(): void
    {
        // given
        $longUrl = 'https://example.com/some-long-url';

        $this->urlRepository
            ->expects($this->exactly(3))
            ->method('findOneByShortUrl')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->createMock(Url::class),
                null
            );
        // when
        $shortCode1 = $this->urlService->generateShortUrlCode($longUrl);
        $shortCode2 = $this->urlService->generateShortUrlCode($longUrl);

        // then
        $this->assertNotSame($shortCode1, $shortCode2, 'For two same long URLs the second generated short code should be generated randomly');
    }

    /**
     * Test base62Encode method.
     *
     * @dataProvider dataProviderForBase62Encode
     *
     * @param int    $num      The number to encode.
     * @param string $expected The expected encoded string.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testBase62Encode(int $num, string $expected): void
    {
        // given
        $reflection = new \ReflectionClass(UrlService::class);
        $method = $reflection->getMethod('base62Encode');

        // when
        $result = $method->invokeArgs($this->urlService, [$num]);

        // then
        $this->assertEquals($expected, $result);
    }

    /**
     * Test generateRandomBase62Code method.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGenerateRandomBase62Code(): void
    {
        // given
        $reflection = new \ReflectionClass($this->urlService);
        $method = $reflection->getMethod('generateRandomBase62Code');

        // when
        $result = $method->invoke($this->urlService);

        // then
        $this->assertSame(7, strlen($result), 'Generated code should be exactly 7 characters long');
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{7}$/', $result, 'Generated code should contain only alphanumeric characters');
    }

    /**
     * Data provider for testBase62Encode().
     *
     * @return array
     */
    public function dataProviderForBase62Encode(): array
    {
        return [
            'Encode 0' => [0, ''],
            'Encode 1' => [1, '1'],
            'Encode 10' => [10, 'a'],
            'Encode 61' => [61, 'Z'],
            'Encode 62' => [62, '10'],
            'Encode 12345' => [12345, '3d7'],
            'Encode 999999' => [999999, '4c91'],
        ];
    }
}
