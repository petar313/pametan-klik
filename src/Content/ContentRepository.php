<?php

namespace App\Content;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Reads the Markdown pages in content/<language>/[<section>/]<slug>.md.
 *
 * All pages are parsed once and cached; the cache key changes whenever a file
 * under content/ is added, removed or modified.
 */
final class ContentRepository
{
    /** @var array<string, Page>|null keyed by "<language>/<section>/<slug>" */
    private ?array $pages = null;

    public function __construct(
        #[Autowire('%kernel.project_dir%/content')]
        private readonly string $contentDir,
        private readonly CacheInterface $cache,
    ) {
    }

    public function find(string $language, ?string $section, string $slug = 'index'): ?Page
    {
        return $this->all()[self::id($language, $section, $slug)] ?? null;
    }

    public function findTranslation(Page $page, string $language): ?Page
    {
        foreach ($this->all() as $candidate) {
            if ($candidate->language === $language && $candidate->key === $page->key) {
                return $candidate;
            }
        }

        return null;
    }

    /** @return list<Page> pages of one section, home page excluded, in menu order */
    public function section(string $language, string $section): array
    {
        $pages = array_filter(
            $this->all(),
            static fn (Page $p): bool => $p->language === $language && $p->section === $section && 'index' !== $p->slug,
        );

        usort($pages, static fn (Page $a, Page $b): int => [$a->order, $a->slug] <=> [$b->order, $b->slug]);

        return $pages;
    }

    /** @return array<string, Page> */
    public function all(): array
    {
        if (null !== $this->pages) {
            return $this->pages;
        }

        $files = iterator_to_array((new Finder())->files()->in($this->contentDir)->name('*.md')->sortByName(), false);
        $fingerprint = hash('xxh128', implode('|', array_map(
            static fn (\SplFileInfo $f): string => $f->getPathname().':'.$f->getMTime(),
            $files,
        )));

        return $this->pages = $this->cache->get('content_'.$fingerprint, fn (): array => $this->parse($files));
    }

    /** @param list<\Symfony\Component\Finder\SplFileInfo> $files */
    private function parse(array $files): array
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new FrontMatterExtension());
        $converter = new MarkdownConverter($environment);

        $pages = [];
        foreach ($files as $file) {
            $parts = explode('/', $file->getRelativePathname());
            $language = array_shift($parts);
            $slug = basename(array_pop($parts), '.md');
            $section = $parts[0] ?? null;

            $result = $converter->convert($file->getContents());
            $meta = $result instanceof RenderedContentWithFrontMatter ? (array) $result->getFrontMatter() : [];

            $pages[self::id($language, $section, $slug)] = new Page(
                language: $language,
                section: $section,
                slug: $slug,
                key: $meta['key'] ?? throw new \UnexpectedValueException(\sprintf('Missing "key" in %s.', $file->getRelativePathname())),
                title: $meta['title'] ?? throw new \UnexpectedValueException(\sprintf('Missing "title" in %s.', $file->getRelativePathname())),
                description: $meta['description'] ?? '',
                html: $result->getContent(),
                order: (int) ($meta['order'] ?? 0),
            );
        }

        return $pages;
    }

    private static function id(string $language, ?string $section, string $slug): string
    {
        return $language.'/'.($section ?? '').'/'.$slug;
    }
}
