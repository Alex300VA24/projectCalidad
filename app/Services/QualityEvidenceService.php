<?php

namespace App\Services;

class QualityEvidenceService
{
    /**
     * @return array<string, array<int, array{curso: string, grupo: string, profesor: string, title: string, detail: string, link: string, preview_url: string}>>
     */
    public function executionReportsByPeriod(): array
    {
        $reportsByPeriod = ['2025-II' => [], '2026-I' => []];
        $path = database_path('data/ejecucion-asignaturas.json');

        if (! is_file($path)) {
            return $reportsByPeriod;
        }

        $periods = json_decode(file_get_contents($path) ?: '[]', true);

        if (! is_array($periods)) {
            return $reportsByPeriod;
        }

        foreach ($periods as $period) {
            if (! is_array($period) || ! isset($reportsByPeriod[$period['periodo'] ?? '']) || ! is_array($period['informes'] ?? null)) {
                continue;
            }

            foreach ($period['informes'] as $report) {
                if (! is_array($report) || ! $this->isSecureUrl($report['link'] ?? null)) {
                    continue;
                }

                $course = (string) ($report['curso'] ?? '');
                $group = (string) ($report['grupo'] ?? '');
                $professor = (string) ($report['profesor'] ?? '');

                $reportsByPeriod[$period['periodo']][] = [
                    'curso' => $course,
                    'grupo' => $group,
                    'profesor' => $professor,
                    'title' => $course,
                    'detail' => "Grupo {$group} · {$professor}",
                    'link' => $report['link'],
                    'preview_url' => $this->drivePreviewUrl($report['link']),
                ];
            }
        }

        return $reportsByPeriod;
    }

    /**
     * @return array<string, array<int, array{title: string, detail: string, link: string, preview_url: string}>>
     */
    public function consolidatedReportsByPeriod(): array
    {
        $reportsByPeriod = ['2025-II' => [], '2026-I' => []];
        $path = database_path('data/consolidado-ejecucion.json');

        if (! is_file($path)) {
            return $reportsByPeriod;
        }

        $reports = json_decode(file_get_contents($path) ?: '[]', true);

        if (! is_array($reports)) {
            return $reportsByPeriod;
        }

        foreach ($reports as $report) {
            if (! is_array($report) || ! isset($reportsByPeriod[$report['periodo'] ?? '']) || ! $this->isSecureUrl($report['link'] ?? null)) {
                continue;
            }

            $reportsByPeriod[$report['periodo']][] = [
                'title' => 'Consolidado de la Ejecución de la Asignatura',
                'detail' => 'Semestre '.$report['periodo'],
                'link' => $report['link'],
                'preview_url' => $this->drivePreviewUrl($report['link']),
            ];
        }

        return $reportsByPeriod;
    }

    /**
     * @return array<string, array<int, array{title: string, link: string, preview_url: string}>>
     */
    public function studentReferences(): array
    {
        $path = database_path('data/referencia_contrareferencia.json');

        if (! is_file($path)) {
            return [];
        }

        $documents = json_decode(file_get_contents($path) ?: '[]', true);

        if (! is_array($documents)) {
            return [];
        }

        $referencesByStudent = [];

        foreach ($documents as $document) {
            if (! is_array($document) || ! is_string($document['alumno'] ?? null) || ! is_string($document['concepto'] ?? null) || ! $this->isSecureUrl($document['link'] ?? null)) {
                continue;
            }

            $student = trim($document['alumno']);
            $title = trim($document['concepto']);

            if ($student === '' || $title === '') {
                continue;
            }

            $referencesByStudent[$student][] = [
                'title' => $title,
                'link' => $document['link'],
                'preview_url' => $this->drivePreviewUrl($document['link']),
            ];
        }

        return $referencesByStudent;
    }

    private function isSecureUrl(mixed $link): bool
    {
        return is_string($link)
            && filter_var($link, FILTER_VALIDATE_URL)
            && parse_url($link, PHP_URL_SCHEME) === 'https';
    }

    private function drivePreviewUrl(string $link): string
    {
        preg_match('~/d/([a-zA-Z0-9_-]+)~', $link, $driveFileId);

        return isset($driveFileId[1])
            ? "https://drive.google.com/file/d/{$driveFileId[1]}/preview"
            : $link;
    }
}
