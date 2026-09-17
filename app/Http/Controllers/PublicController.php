<?php

namespace App\Http\Controllers;

use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use PhpOffice\PhpWord\Element\ListItemRun;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;

class PublicController extends Controller
{
    public function showDoc(string $slug): View
    {
        $map = [
            'faq' => 'FAQ.docx',
            'hubungi-kami' => 'HUBUNGI KAMI.docx',
            'refund-policy' => 'Refund Policy.docx',
            'syarat-ketentuan' => 'Syarat & Ketentuan.docx',
        ];

        $titles = [
            'faq' => 'FAQ — Pertanyaan yang Sering Diajukan',
            'hubungi-kami' => 'Hubungi Kami',
            'refund-policy' => 'Kebijakan Pengembalian Dana',
            'syarat-ketentuan' => 'Syarat & Ketentuan Penggunaan',
        ];

        $icons = [
            'faq' => '❓',
            'hubungi-kami' => '📞',
            'refund-policy' => '💰',
            'syarat-ketentuan' => '📋',
        ];

        $colors = [
            'faq' => ['from' => 'blue-600', 'to' => 'indigo-600', 'badge' => 'blue'],
            'hubungi-kami' => ['from' => 'emerald-600', 'to' => 'teal-600', 'badge' => 'emerald'],
            'refund-policy' => ['from' => 'orange-600', 'to' => 'amber-600', 'badge' => 'orange'],
            'syarat-ketentuan' => ['from' => 'violet-600', 'to' => 'purple-600', 'badge' => 'violet'],
        ];

        if (! isset($map[$slug])) {
            abort(404, 'Document not found');
        }

        $filePath = public_path($map[$slug]);
        if (! file_exists($filePath)) {
            abort(404, 'File not found');
        }

        $reader = IOFactory::createReader('Word2007');
        $document = $reader->load($filePath);

        $sections = $document->getSections();
        $section = $sections[0] ?? null;
        $elements = $section ? $section->getElements() : [];

        $html = $this->renderElements($elements);

        // Special handling for FAQ – build accordion directly from raw text
        if ($slug === 'faq') {
            $html = $this->buildFaqAccordion($elements);
        }

        $title = $titles[$slug] ?? ucfirst(str_replace('-', ' ', $slug));

        if ($slug === 'syarat-ketentuan') {
            return view('public.syarat-ketentuan');
        }

        $view = 'public.doc';
        if ($slug === 'faq') {
            $view = 'public.faq';
        } elseif ($slug === 'hubungi-kami') {
            $view = 'public.hubungi';
        }

        return view($view, [
            'title' => $title,
            'content' => new HtmlString($html),
            'slug' => $slug,
            'icon' => $icons[$slug] ?? '📄',
            'color' => $colors[$slug] ?? ['from' => 'gray-600', 'to' => 'gray-700', 'badge' => 'gray'],
        ]);
    }

    private function buildFaqAccordion(array $elements): string
    {
        // Extract all text from elements
        $lines = [];
        foreach ($elements as $el) {
            if (method_exists($el, 'getText')) {
                $text = trim($el->getText());
                if ($text !== '') {
                    $lines[] = $text;
                }
            }
            if ($el instanceof TextBreak) {
                $lines[] = '';
            }
        }

        // First line is the title (skip it)
        $pairs = [];
        $currentQuestion = '';
        foreach ($lines as $line) {
            if ($line === '' || $line === 'FAQ — Pertanyaan yang Sering Diajukan') {
                continue;
            }
            // Lines ending with '?' are questions
            if (mb_substr($line, -1) === '?') {
                $currentQuestion = $line;
            } elseif ($currentQuestion !== '') {
                $pairs[] = ['q' => $currentQuestion, 'a' => $line];
                $currentQuestion = '';
            }
        }

        // Build HTML
        $html = '';
        foreach ($pairs as $i => $pair) {
            $html .= '<div class="mb-3">';
            $html .= '<details class="bg-white/60 rounded-xl border border-gray-200 hover:border-indigo-300 transition-colors group">';
            $html .= '<summary class="flex items-center justify-between p-5 cursor-pointer">';
            $html .= '<span class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors pr-4">'.e($pair['q']).'</span>';
            $html .= '<svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 chevron flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            $html .= '</summary>';
            $html .= '<div class="answer px-5 pb-5 text-gray-600 leading-relaxed border-t border-gray-100 pt-4">'.e($pair['a']).'</div>';
            $html .= '</details>';
            $html .= '</div>';
        }

        return $html;
    }

    private function renderElements(array $elements): string
    {
        $html = [];
        $listBuffer = [];
        $textBuf = '';

        $flushText = function () use (&$textBuf, &$html) {
            $textBuf = trim($textBuf);
            if ($textBuf !== '') {
                $html[] = '<p class="text-gray-700 leading-relaxed mb-4">'.$textBuf.'</p>';
                $textBuf = '';
            }
        };

        $flushList = function () use (&$listBuffer, &$html) {
            if (! empty($listBuffer)) {
                $items = '';
                foreach ($listBuffer as $item) {
                    $items .= '<li class="flex items-start gap-3 py-1.5">
                        <span class="mt-1.5 w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span>
                        <span class="text-gray-700">'.e($item).'</span>
                    </li>';
                }
                $html[] = '<ul class="space-y-1 mb-5 pl-1">'.$items.'</ul>';
                $listBuffer = [];
            }
        };

        foreach ($elements as $el) {
            if ($el instanceof TextBreak) {
                $flushText();
                $flushList();

                continue;
            }

            if ($el instanceof ListItemRun) {
                $flushText();
                $text = $el->getText();
                $text = trim($text);
                if ($text !== '') {
                    $listBuffer[] = $text;
                }

                continue;
            }

            $flushList();

            if ($el instanceof TextRun) {
                $text = $el->getText();
                $text = trim($text);
                if ($text === '') {
                    continue;
                }

                // Detect if this looks like a heading or bold section title
                $fontStyle = $el->getFontStyle();
                $isBold = $fontStyle && method_exists($fontStyle, 'isBold') && $fontStyle->isBold();
                $size = $fontStyle && method_exists($fontStyle, 'getSize') ? $fontStyle->getSize() : null;

                // Check for pattern: starts with emoji or number followed by dot
                $isSectionTitle = preg_match('/^\d+\.\s/', $text)
                    || preg_match('/^(📧|📱|📍|☎|📞|💬|🕐|⏰)/u', $text)
                    || preg_match('/^(Customer Support|Jam layanan|Ketentuan Umum|Pengajuan Refund|Pembayaran Berlangganan|Transaksi Ganda|Persetujuan Refund|Waktu Pengembalian)/', $text);

                if ($isBold && mb_strlen($text) > 3 && mb_strlen($text) < 80 && ! preg_match('/^[A-Z]/', $text) === false) {
                    $textBuf .= '|||HEADING|||'.$text;
                } elseif ($isSectionTitle || ($size && $size >= 24)) {
                    $flushText();
                    $html[] = '<div class="mt-8 mb-4 first:mt-0">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded-full flex-shrink-0"></span>
                            '.e($text).'
                        </h3>
                    </div>';
                } else {
                    $escaped = e($text);
                    $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong class="font-semibold text-gray-900">$1</strong>', $escaped);
                    $textBuf .= $escaped;
                }
            }
        }

        $flushText();
        $flushList();

        // Process heading markers
        $result = implode("\n", $html);
        $result = preg_replace_callback('/\|\|\|HEADING\|\|\|(.+?)</', function ($m) {
            return '</p><h3 class="text-xl font-bold text-gray-900 mt-8 mb-3 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded-full flex-shrink-0"></span>'
                .e(trim($m[1])).'</h3><p class="text-gray-700 leading-relaxed mb-4">';
        }, $result);

        // Clean up empty paragraphs
        $result = preg_replace('/<p class="[^"]*">\s*<\/p>/', '', $result);
        // Clean up consecutive paragraph openers
        $result = str_replace('</p><p class="text-gray-700 leading-relaxed mb-4">', "\n\n", $result);

        return $result;
    }

    public function landing(): View
    {
        return view('public.landing');
    }
}
