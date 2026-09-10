/**
 * Penggambar Data & Grafik.
 *
 * Dipakai dua tempat: pratinjau langsung di form CMS, dan halaman embed yang
 * ditanam editor ke dalam laporan lewat <iframe>. Keduanya memanggil
 * window.renderDataVisualizationChart, jadi grafik yang dilihat editor saat
 * menyusun data identik dengan yang dilihat pembaca.
 *
 * Bagian kedua berkas ini menangani seleksi ala spreadsheet pada tabel data di
 * form: menyeret beberapa sel, menghapus isinya sekaligus, dan menempel dari
 * Excel atau Google Sheets.
 */

import Chart from 'chart.js/auto';
import { Flow, SankeyController } from 'chartjs-chart-sankey';

Chart.register(SankeyController, Flow);

const visualizationColors = ['#376A64', '#D95C4F', '#E3A72F', '#5A7FB8', '#8A63A9', '#4F9D69', '#C96B9B', '#7C6F64', '#22A6B3'];

window.renderDataVisualizationChart = (canvas, type, chartData) => {
    if (!canvas || !chartData?.columns || !chartData?.rows) return;

    Chart.getChart(canvas)?.destroy();
    const chartContainer = canvas.parentElement;
    const previousGrid = chartContainer?.querySelector('[data-area-chart-grid]');
    previousGrid?.querySelectorAll('canvas').forEach((gridCanvas) => Chart.getChart(gridCanvas)?.destroy());
    previousGrid?.remove();
    canvas.style.display = '';

    const labels = chartData.rows.map((row) => row[0]);
    const series = chartData.columns.slice(1);
    const isCircular = type === 'pie' || type === 'doughnut';
    const isLine = type === 'line' || type === 'area';
    const isBarChart = type === 'bar' || type === 'column';
    const topText = String(chartData.top_text || '').trim();
    const bottomText = String(chartData.bottom_text || '').trim();
    const titleAlignment = { left: 'start', center: 'center', right: 'end' }[chartData.top_align] || 'center';
    const footerAlignment = { left: 'start', center: 'center', right: 'end' }[chartData.bottom_align] || 'center';
    const topFontSize = Math.min(72, Math.max(10, Number(chartData.top_font_size) || 18));
    const bottomFontSize = Math.min(72, Math.max(10, Number(chartData.bottom_font_size) || 13));
    const topFontWeight = chartData.top_font_weight || 'bold';
    const bottomFontWeight = chartData.bottom_font_weight || 'normal';
    const topFontFamily = chartData.top_font_family || 'Poppins';
    const bottomFontFamily = chartData.bottom_font_family || 'Poppins';
    const topFontStyle = chartData.top_italic ? 'italic' : 'normal';
    const bottomFontStyle = chartData.bottom_italic ? 'italic' : 'normal';
    const showLegend = chartData.show_legend !== false;
    const legendPosition = chartData.legend_position === 'top' ? 'top' : 'bottom';
    const compactPreview = chartData.compact_preview === true;
    const valueAxisTicks = Array.isArray(chartData.value_axis_ticks)
        ? [...new Set(chartData.value_axis_ticks
            .filter((value) => value !== null && value !== undefined && value !== '')
            .map(Number)
            .filter(Number.isFinite))].sort((first, second) => first - second)
        : [];
    const showValueAxisLine = chartData.show_value_axis_line !== false;
    const chartWidth = chartContainer?.clientWidth || canvas.clientWidth || 0;
    const isNarrowChart = chartWidth > 0 && chartWidth < 640;
    const responsiveTopFontSize = isNarrowChart ? Math.min(topFontSize, 14) : topFontSize;
    const responsiveBottomFontSize = isNarrowChart ? Math.min(bottomFontSize, 10) : bottomFontSize;
    const responsiveAxisFontSize = isNarrowChart ? 10 : (compactPreview ? 9 : 13);
    const responsiveAxisPadding = isNarrowChart ? 6 : (compactPreview ? 4 : 12);
    const responsiveValueAxisPadding = isNarrowChart ? 6 : (compactPreview ? 4 : 10);
    const responsiveMaxBarThickness = isNarrowChart ? 72 : 100;
    const wrapChartText = (text, fontSize, fontWeight, fontFamily, fontStyle) => {
        if (!isNarrowChart || !text) return text;

        const context = canvas.getContext('2d');
        if (!context) return text;

        const words = text.split(/\s+/).filter(Boolean);
        const lines = [];
        let currentLine = '';
        const maximumLineWidth = Math.max(160, chartWidth - 56);

        context.save();
        context.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;

        words.forEach((word) => {
            const candidate = currentLine ? `${currentLine} ${word}` : word;

            if (currentLine && context.measureText(candidate).width > maximumLineWidth) {
                lines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = candidate;
            }
        });

        if (currentLine) lines.push(currentLine);
        context.restore();

        return lines.length > 1 ? lines : text;
    };
    const responsiveTopText = wrapChartText(topText, responsiveTopFontSize, topFontWeight, topFontFamily, topFontStyle);
    const responsiveBottomText = wrapChartText(bottomText, responsiveBottomFontSize, bottomFontWeight, bottomFontFamily, bottomFontStyle);

    if (type === 'area-grid') {
        canvas.style.display = 'none';
        const grid = document.createElement('div');
        grid.dataset.areaChartGrid = '';
        grid.className = 'flex h-full min-h-0 w-full flex-col';

        if (topText) {
            const heading = document.createElement('h3');
            heading.className = 'mb-3 shrink-0 text-center text-lg font-semibold text-gray-900';
            heading.textContent = topText;
            heading.style.textAlign = chartData.top_align || 'center';
            heading.style.fontSize = `${responsiveTopFontSize}px`;
            heading.style.fontWeight = topFontWeight;
            heading.style.fontFamily = topFontFamily;
            heading.style.fontStyle = topFontStyle;
            heading.style.color = '#000000';
            grid.appendChild(heading);
        }

        const entries = chartData.rows
            .map((row, index) => ({
                label: String(row[0] || `Data ${index + 1}`),
                value: Math.max(0, Number(row[1]) || 0),
            }))
            .filter((entry) => entry.value > 0)
            .sort((a, b) => b.value - a.value);

        const treemap = document.createElement('div');
        treemap.className = 'relative min-h-0 flex-1 overflow-hidden bg-white';
        treemap.style.aspectRatio = '16 / 9';

        const layoutTreemap = (items, x = 0, y = 0, width = 160, height = 90) => {
            if (!items.length) return [];
            if (items.length === 1) return [{ ...items[0], x, y, width, height }];

            const total = items.reduce((sum, item) => sum + item.value, 0);
            let splitIndex = 1;
            let runningTotal = items[0].value;
            let smallestDifference = Math.abs(total / 2 - runningTotal);

            for (let index = 1; index < items.length - 1; index += 1) {
                runningTotal += items[index].value;
                const difference = Math.abs(total / 2 - runningTotal);
                if (difference < smallestDifference) {
                    splitIndex = index + 1;
                    smallestDifference = difference;
                }
            }

            const firstGroup = items.slice(0, splitIndex);
            const secondGroup = items.slice(splitIndex);
            const firstTotal = firstGroup.reduce((sum, item) => sum + item.value, 0);
            const ratio = firstTotal / total;

            if (width >= height) {
                const firstWidth = width * ratio;
                return [
                    ...layoutTreemap(firstGroup, x, y, firstWidth, height),
                    ...layoutTreemap(secondGroup, x + firstWidth, y, width - firstWidth, height),
                ];
            }

            const firstHeight = height * ratio;
            return [
                ...layoutTreemap(firstGroup, x, y, width, firstHeight),
                ...layoutTreemap(secondGroup, x, y + firstHeight, width, height - firstHeight),
            ];
        };

        layoutTreemap(entries).forEach((entry, index) => {
            const tile = document.createElement('div');
            tile.className = 'group absolute flex items-center justify-center overflow-visible border border-white text-center font-bold uppercase leading-tight text-white';
            tile.style.left = `${entry.x / 1.6}%`;
            tile.style.top = `${entry.y / 0.9}%`;
            tile.style.width = `${entry.width / 1.6}%`;
            tile.style.height = `${entry.height / 0.9}%`;
            tile.style.backgroundColor = visualizationColors[index % visualizationColors.length];
            tile.style.fontSize = compactPreview ? '9px' : `${Math.max(11, Math.min(20, Math.sqrt(entry.width * entry.height) * 0.42))}px`;

            const label = document.createElement('span');
            label.className = 'max-w-[90%] break-words px-1';
            label.textContent = entry.label;
            tile.appendChild(label);

            const tooltip = document.createElement('span');
            tooltip.className = 'pointer-events-none absolute left-1/2 top-1/2 z-10 hidden -translate-x-1/2 -translate-y-[calc(100%+1.5rem)] whitespace-nowrap bg-white px-3 py-2 text-xs font-bold normal-case text-gray-800 shadow-lg group-hover:block';
            tooltip.textContent = `${entry.value.toLocaleString('id-ID')} ${chartData.columns[1] || ''}`.trim();
            tile.appendChild(tooltip);
            treemap.appendChild(tile);
        });

        if (!entries.length) {
            const empty = document.createElement('p');
            empty.className = 'flex h-full items-center justify-center text-sm text-gray-400';
            empty.textContent = 'Belum ada nilai untuk ditampilkan.';
            treemap.appendChild(empty);
        }

        grid.appendChild(treemap);

        if (bottomText) {
            const footer = document.createElement('p');
            footer.className = 'mt-2 shrink-0 text-center text-sm text-gray-600';
            footer.textContent = bottomText;
            footer.style.textAlign = chartData.bottom_align || 'center';
            footer.style.fontSize = `${responsiveBottomFontSize}px`;
            footer.style.fontWeight = bottomFontWeight;
            footer.style.fontFamily = bottomFontFamily;
            footer.style.fontStyle = bottomFontStyle;
            grid.appendChild(footer);
        }

        chartContainer?.appendChild(grid);
        return;
    }

    if (type === 'sankey') {
        const nodeColor = (node) => {
            const text = String(node || '');
            const hash = [...text].reduce((total, character) => total + character.charCodeAt(0), 0);
            return visualizationColors[hash % visualizationColors.length];
        };
        const flows = chartData.rows
            .filter((row) => row[0] && row[1] && Number(row[2]) > 0)
            .map((row) => ({ from: String(row[0]), to: String(row[1]), flow: Number(row[2]) }));

        new Chart(canvas, {
            type: 'sankey',
            data: {
                datasets: [{
                    label: chartData.columns[2] || 'Nilai',
                    data: flows,
                    colorFrom: (context) => nodeColor(context.dataset.data[context.dataIndex]?.from),
                    colorTo: (context) => nodeColor(context.dataset.data[context.dataIndex]?.to),
                    colorMode: 'gradient',
                    alpha: 0.55,
                    size: 'max',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: { display: Boolean(topText), text: responsiveTopText, position: 'top', align: titleAlignment, color: '#000000', font: { family: topFontFamily, size: responsiveTopFontSize, weight: topFontWeight, style: topFontStyle } },
                    subtitle: { display: Boolean(bottomText), text: responsiveBottomText, position: 'bottom', align: footerAlignment, color: '#6b7280', font: { family: bottomFontFamily, size: responsiveBottomFontSize, weight: bottomFontWeight, style: bottomFontStyle } },
                },
            },
        });
        return;
    }

    const datasets = isCircular
        ? [{
            label: series[0] || 'Nilai',
            data: chartData.rows.map((row) => Number(row[1]) || 0),
            backgroundColor: labels.map((_, index) => visualizationColors[index % visualizationColors.length]),
            borderWidth: 0,
        }]
        : series.map((name, seriesIndex) => ({
            label: name,
            data: chartData.rows.map((row) => Number(row[seriesIndex + 1]) || 0),
            backgroundColor: isLine ? `${visualizationColors[seriesIndex % visualizationColors.length]}35` : visualizationColors[seriesIndex % visualizationColors.length],
            borderColor: visualizationColors[seriesIndex % visualizationColors.length],
            borderWidth: isLine ? 2 : 0,
            pointRadius: isLine ? 3 : 0,
            tension: 0.25,
            fill: type === 'area',
            ...(isBarChart ? {
                barPercentage: 0.72,
                categoryPercentage: 0.72,
                maxBarThickness: responsiveMaxBarThickness,
                borderRadius: 0,
                borderSkipped: false,
            } : {}),
        }));

    const categoryScale = {
        grid: { display: false },
        border: { display: true, color: '#111827', width: 1 },
        ticks: {
            color: '#111827',
            padding: responsiveAxisPadding,
            autoSkip: true,
            maxRotation: 0,
            ...(compactPreview ? { maxTicksLimit: 5 } : {}),
            font: { size: responsiveAxisFontSize },
        },
    };
    const valueScale = {
        beginAtZero: valueAxisTicks.length < 2,
        grid: { color: '#D1D5DB', lineWidth: 1, drawTicks: false },
        border: { display: showValueAxisLine, color: '#111827', width: 1 },
        ...(valueAxisTicks.length >= 2 ? {
            min: valueAxisTicks[0],
            max: valueAxisTicks[valueAxisTicks.length - 1],
            afterBuildTicks: (scale) => {
                scale.ticks = valueAxisTicks.map((value) => ({ value }));
            },
        } : {}),
        ticks: {
            color: '#111827',
            padding: responsiveValueAxisPadding,
            callback: (value) => Number(value).toLocaleString('id-ID'),
            ...(valueAxisTicks.length >= 2 ? { autoSkip: false } : {}),
            ...(compactPreview ? { maxTicksLimit: 4 } : {}),
            font: { size: responsiveAxisFontSize },
        },
    };

    new Chart(canvas, {
        type: type === 'line' || type === 'area' ? 'line' : type === 'bar' || type === 'column' ? 'bar' : type,
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { left: isNarrowChart ? 0 : 4, right: isNarrowChart ? 6 : 12, bottom: isNarrowChart ? 0 : 4 } },
            plugins: {
                legend: { display: showLegend, position: legendPosition },
                title: { display: Boolean(topText), text: responsiveTopText, position: 'top', align: titleAlignment, color: '#000000', font: { family: topFontFamily, size: responsiveTopFontSize, weight: topFontWeight, style: topFontStyle } },
                subtitle: { display: Boolean(bottomText), text: responsiveBottomText, position: 'bottom', align: footerAlignment, color: '#6b7280', font: { family: bottomFontFamily, size: responsiveBottomFontSize, weight: bottomFontWeight, style: bottomFontStyle } },
                tooltip: { mode: isCircular ? 'nearest' : 'index', intersect: isCircular },
            },
            indexAxis: type === 'bar' ? 'y' : 'x',
            scales: isCircular
                ? {}
                : type === 'bar'
                    ? { x: valueScale, y: categoryScale }
                    : { x: categoryScale, y: valueScale },
        },
    });
};

const spreadsheetSelection = {
    root: null,
    start: null,
    end: null,
    dragging: false,
    moved: false,
};

const spreadsheetCoordinates = (cell) => ({
    row: Number(cell?.dataset.row ?? -1),
    column: Number(cell?.dataset.column ?? -1),
});

const spreadsheetCellsInSelection = () => {
    if (!spreadsheetSelection.root || !spreadsheetSelection.start || !spreadsheetSelection.end) return [];

    const start = spreadsheetCoordinates(spreadsheetSelection.start);
    const end = spreadsheetCoordinates(spreadsheetSelection.end);
    const minRow = Math.min(start.row, end.row);
    const maxRow = Math.max(start.row, end.row);
    const minColumn = Math.min(start.column, end.column);
    const maxColumn = Math.max(start.column, end.column);

    return Array.from(spreadsheetSelection.root.querySelectorAll('[data-spreadsheet-cell]')).filter((cell) => {
        const coordinates = spreadsheetCoordinates(cell);
        return coordinates.row >= minRow
            && coordinates.row <= maxRow
            && coordinates.column >= minColumn
            && coordinates.column <= maxColumn;
    });
};

const clearSpreadsheetSelection = () => {
    spreadsheetSelection.root?.querySelectorAll('[data-spreadsheet-cell]').forEach((cell) => {
        cell.classList.remove('is-selected', 'is-selection-start');
    });

    spreadsheetSelection.root = null;
    spreadsheetSelection.start = null;
    spreadsheetSelection.end = null;
    spreadsheetSelection.dragging = false;
    spreadsheetSelection.moved = false;
};

const clearSpreadsheetCellValues = () => {
    spreadsheetCellsInSelection().forEach((cell) => {
        const input = cell.querySelector('input');
        if (!input) return;

        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
};

const isEditingSingleSpreadsheetCell = (target, cells = spreadsheetCellsInSelection()) => {
    const input = target?.closest?.('[data-spreadsheet-cell] input');

    return cells.length === 1 && Boolean(input) && cells[0].contains(input);
};

const paintSpreadsheetSelection = () => {
    if (!spreadsheetSelection.root) return;

    spreadsheetSelection.root.querySelectorAll('[data-spreadsheet-cell]').forEach((cell) => {
        cell.classList.remove('is-selected', 'is-selection-start');
    });
    spreadsheetCellsInSelection().forEach((cell) => cell.classList.add('is-selected'));
    spreadsheetSelection.start?.classList.add('is-selection-start');
};

document.addEventListener('pointerdown', (event) => {
    if (event.button !== 0) return;

    const cell = event.target.closest?.('[data-spreadsheet-cell]');
    const root = cell?.closest?.('[data-spreadsheet]');
    if (!cell || !root) {
        clearSpreadsheetSelection();

        return;
    }

    spreadsheetSelection.root = root;
    spreadsheetSelection.start = cell;
    spreadsheetSelection.end = cell;
    spreadsheetSelection.dragging = true;
    spreadsheetSelection.moved = false;
    paintSpreadsheetSelection();
});

document.addEventListener('focusin', (event) => {
    if (!spreadsheetSelection.root || spreadsheetSelection.root.contains(event.target)) return;

    clearSpreadsheetSelection();
});

document.addEventListener('pointermove', (event) => {
    if (!spreadsheetSelection.dragging || !spreadsheetSelection.root) return;

    const pointedElement = document.elementFromPoint(event.clientX, event.clientY);
    const cell = pointedElement?.closest?.('[data-spreadsheet-cell]');
    if (!cell || !spreadsheetSelection.root.contains(cell) || cell === spreadsheetSelection.end) return;

    event.preventDefault();
    spreadsheetSelection.end = cell;
    spreadsheetSelection.moved = true;
    paintSpreadsheetSelection();
});

document.addEventListener('pointerup', () => {
    if (!spreadsheetSelection.dragging) return;

    spreadsheetSelection.dragging = false;
});

document.addEventListener('keydown', async (event) => {
    if (spreadsheetSelection.root && !spreadsheetSelection.root.isConnected) {
        clearSpreadsheetSelection();
    }

    const cells = spreadsheetCellsInSelection();

    if (['Backspace', 'Delete'].includes(event.key) && cells.length) {
        if (isEditingSingleSpreadsheetCell(event.target, cells)) return;

        event.preventDefault();
        clearSpreadsheetCellValues();

        return;
    }

    const shortcut = event.key.toLowerCase();
    if (!(event.ctrlKey || event.metaKey) || !['c', 'x'].includes(shortcut)) return;

    if (!cells.length || !spreadsheetSelection.root) return;
    if (isEditingSingleSpreadsheetCell(event.target, cells)) return;

    const start = spreadsheetCoordinates(spreadsheetSelection.start);
    const end = spreadsheetCoordinates(spreadsheetSelection.end);
    const minRow = Math.min(start.row, end.row);
    const maxRow = Math.max(start.row, end.row);
    const minColumn = Math.min(start.column, end.column);
    const maxColumn = Math.max(start.column, end.column);
    const values = [];

    for (let row = minRow; row <= maxRow; row += 1) {
        const rowValues = [];
        for (let column = minColumn; column <= maxColumn; column += 1) {
            const cell = spreadsheetSelection.root.querySelector(`[data-spreadsheet-cell][data-row="${row}"][data-column="${column}"]`);
            rowValues.push(cell?.querySelector('input')?.value ?? '');
        }
        values.push(rowValues.join('\t'));
    }

    event.preventDefault();
    await navigator.clipboard?.writeText(values.join('\n'));

    if (shortcut === 'x') {
        clearSpreadsheetCellValues();
    }
});

document.addEventListener('paste', (event) => {
    if (spreadsheetSelection.root && !spreadsheetSelection.root.isConnected) {
        clearSpreadsheetSelection();
    }

    if (!spreadsheetSelection.root || !spreadsheetSelection.start) return;

    const text = event.clipboardData?.getData('text/plain');
    if (!text) return;

    const cells = spreadsheetCellsInSelection();
    const isTabularPaste = /[\t\r\n]/.test(text);
    if (!isTabularPaste && isEditingSingleSpreadsheetCell(event.target, cells)) return;

    const start = spreadsheetCoordinates(spreadsheetSelection.start);
    const pastedRows = text.replace(/\r/g, '').split('\n').filter((row, index, rows) => row !== '' || index < rows.length - 1);
    let changed = false;

    pastedRows.forEach((rowText, rowOffset) => {
        rowText.split('\t').forEach((value, columnOffset) => {
            const cell = spreadsheetSelection.root.querySelector(`[data-spreadsheet-cell][data-row="${start.row + rowOffset}"][data-column="${start.column + columnOffset}"]`);
            const input = cell?.querySelector('input');
            if (!input) return;

            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            changed = true;
        });
    });

    if (changed) event.preventDefault();
});

