<?php

namespace Base\Module\Src\Options\Providers;

use Base\Module\Src\Options\Interface\OptionProvider;

class TableProvider implements OptionProvider
{
    private array $columns = [];
    private array $childColumns = [];
    private array $rows = [];
    private string $empty = '';
    private string $expandLabel = '';
    private bool $showHeader = false;

    public function getType(): string
    {
        return 'table';
    }

    public function render(array $option, string $moduleId): string
    {
        $params = $option['params'] ?? [];
        $columns = $params['columns'] ?? $this->columns;
        $childColumns = $params['childColumns'] ?? $this->childColumns;
        if (empty($childColumns)) {
            $childColumns = $columns;
        }
        $rows = $params['rows'] ?? $this->rows;
        $showHeader = (bool)($params['showHeader'] ?? $this->showHeader);

        $colspan = max(1, count($columns));
        $childColspan = max(1, count($childColumns));

        $html = '<tr>';
        $html .= '<td colspan="2">';

        if ($showHeader && !empty($option['name'] ?? '')) {
            $html .= '<div class="base-module-table-option-head">' .
                htmlspecialcharsbx((string)$option['name']) . '</div>';
        }

        $html .= '<table class="base-module-table-option" style="width:100%;border-collapse:collapse">';
        $html .= '<thead><tr>';
        foreach ($columns as $column) {
            $html .= '<th class="base-module-table-option-th">' .
                htmlspecialcharsbx((string)$column) . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        if (empty($rows)) {
            $html .= '<tr><td colspan="' . $colspan . '" class="base-module-table-option-empty">' .
                htmlspecialcharsbx((string)($params['empty'] ?? $this->empty)) . '</td></tr>';
        }

        foreach ($rows as $row) {
            $children = $row['children'] ?? [];
            $hasChildren = !empty($children);

            $classes = 'base-module-table-option-row';
            if ($hasChildren) {
                $classes .= ' base-module-table-option-expandable';
            }
            if ($row['highlight'] ?? false) {
                $classes .= ' base-module-table-option-self';
            }

            $html .= '<tr class="' . $classes . '">';
            $html .= $this->renderCells($row['cells'] ?? []);
            $html .= '</tr>';

            if ($hasChildren) {
                $html .= '<tr class="base-module-table-option-sub" style="display:none">';
                $html .= '<td colspan="' . $childColspan . '">';
                $html .= '<table class="base-module-table-option-subtable" style="width:100%;border-collapse:collapse">';
                $html .= '<thead><tr>';
                foreach ($childColumns as $column) {
                    $html .= '<th class="base-module-table-option-subth">' .
                        htmlspecialcharsbx((string)$column) . '</th>';
                }
                $html .= '</tr></thead>';
                $html .= '<tbody>';
                foreach ($children as $child) {
                    $classes = 'base-module-table-option-subrow';
                    if ($child['highlight'] ?? false) {
                        $classes .= ' base-module-table-option-self';
                    }
                    $html .= '<tr class="' . $classes . '">';
                    $html .= $this->renderCells($child['cells'] ?? [], $child['highlight'] ?? false);
                    $html .= '</tr>';
                }
                $html .= '</tbody>';
                $html .= '</table>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';

        if (!empty($params['expandLabel'] ?? $this->expandLabel)) {
            $html .= '<div class="base-module-table-option-hint">' .
                htmlspecialcharsbx((string)($params['expandLabel'] ?? $this->expandLabel)) . '</div>';
        }

        $html .= $this->renderStyle();
        $html .= $this->renderScript();
        $html .= '</td>';
        $html .= '</tr>';

        return $html;
    }

    public function save(array $option, string $moduleId, mixed $value): void
    {
        // Опция таблицы не сохраняет значений.
    }

    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function setChildColumns(array $childColumns): self
    {
        $this->childColumns = $childColumns;
        return $this;
    }

    public function setRows(array $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    public function setEmpty(string $empty): self
    {
        $this->empty = $empty;
        return $this;
    }

    public function setExpandLabel(string $expandLabel): self
    {
        $this->expandLabel = $expandLabel;
        return $this;
    }

    public function setShowHeader(bool $showHeader): self
    {
        $this->showHeader = $showHeader;
        return $this;
    }

    public function getParamsToArray(): array
    {
        return [
            'columns' => $this->columns,
            'childColumns' => $this->childColumns,
            'rows' => $this->rows,
            'empty' => $this->empty,
            'expandLabel' => $this->expandLabel,
            'showHeader' => $this->showHeader,
        ];
    }

    /**
     * @param array $cells
     * @param bool $highlight
     * @return string
     */
    private function renderCells(array $cells, bool $highlight = false): string
    {
        $html = '';
        foreach ($cells as $cell) {
            $tdClass = 'base-module-table-option-cell';
            if ($highlight) {
                $tdClass .= ' base-module-table-option-self';
            }
            if (is_array($cell)) {
                $status = $cell['status'] ?? '';
                $text = htmlspecialcharsbx((string)($cell['text'] ?? ''));
                if ($status === 'ok') {
                    $html .= '<td class="' . $tdClass . ' base-module-table-option-status-ok">&#10004; ' . $text . '</td>';
                } elseif ($status === 'no') {
                    $html .= '<td class="' . $tdClass . ' base-module-table-option-status-no">&#10008; ' . $text . '</td>';
                } else {
                    $html .= '<td class="' . $tdClass . '">' . $text . '</td>';
                }
            } else {
                $html .= '<td class="' . $tdClass . '">' . htmlspecialcharsbx((string)$cell) . '</td>';
            }
        }
        return $html;
    }

    /**
     * @return string
     */
    private function renderStyle(): string
    {
        return '<style>
.base-module-table-option { border-collapse: collapse; }
.base-module-table-option-th {
    background: #f2f2f2; border: 1px solid #d6d6d6; padding: 4px 8px; text-align: left; font-weight: bold;
}
.base-module-table-option-expandable { cursor: pointer; }
.base-module-table-option-cell { background: transparent; }
.base-module-table-option td, .base-module-table-option-subtable td {
    border: 1px solid #e2e2e2; padding: 4px 8px;
}
.base-module-table-option-subtable th {
    background: #fafafa; border: 1px solid #e2e2e2; padding: 3px 7px; text-align: left;
}
.base-module-table-option tr.base-module-table-option-self td,
.base-module-table-option td.base-module-table-option-self,
.base-module-table-option-subtable tr.base-module-table-option-self td,
.base-module-table-option-subtable td.base-module-table-option-self { background: #eaf3ff; }
.base-module-table-option-sub td { background: #fcfcfc; }
.base-module-table-option-status-ok { color: #2e7d32; white-space: nowrap; }
.base-module-table-option-status-no { color: #c62828; white-space: nowrap; }
.base-module-table-option-head { font-weight: bold; margin-bottom: 6px; }
.base-module-table-option-empty { color: #8a8a8a; text-align: center; padding: 8px; }
.base-module-table-option-hint { color: #8a8a8a; font-size: 11px; margin-top: 4px; }
</style>';
    }

    /**
     * @return string
     */
    private function renderScript(): string
    {
        return '<script>
(function () {
    var rows = document.querySelectorAll(".base-module-table-option-expandable");
    for (var i = 0; i < rows.length; i++) {
        if (rows[i].getAttribute("data-toggle-bound")) {
            continue;
        }
        rows[i].setAttribute("data-toggle-bound", "1");
        rows[i].addEventListener("click", function () {
            var sub = this.nextElementSibling;
            while (sub && !sub.classList.contains("base-module-table-option-sub")) {
                sub = sub.nextElementSibling;
            }
            if (sub) {
                sub.style.display = (sub.style.display === "none") ? "" : "none";
            }
        });
    }
})();
</script>';
    }
}
