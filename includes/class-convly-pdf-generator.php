<?php
/**
 * PDF Report Generator
 *
 * @package    Convly
 * @subpackage Convly/includes
 */

class Convly_PDF_Generator {

    /**
     * Generate PDF report
     */
    public function generate_report($data, $date_range) {
        // For now, we'll generate a simple HTML report that can be converted to PDF
        // In production, you would use a library like TCPDF or mPDF
        
        $this->output_html_report($data, $date_range);
    }

    /**
     * Output HTML report (temporary solution)
     */
    private function output_html_report($data, $date_range) {
        // Set headers for download
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="convly-report-' . date('Y-m-d') . '.html"');

        $site_name = get_bloginfo('name');
        $date_range_text = $this->format_date_range($date_range);

        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Convly Report - <?php echo esc_html($site_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #0073aa;
        }
        .header h1 {
            color: #0073aa;
            margin: 10px 0;
        }
        .summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        .metric {
            background: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .metric-value {
            font-size: 32px;
            font-weight: bold;
            color: #0073aa;
        }
        .metric-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .high-rate {
            color: #46b450;
            font-weight: bold;
        }
        .medium-rate {
            color: #ffb900;
            font-weight: bold;
        }
        .low-rate {
            color: #dc3232;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .header {
                page-break-after: avoid;
            }
            table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Conversion Rate Report</h1>
        <h2><?php echo esc_html($site_name); ?></h2>
        <p>Report Period: <?php echo esc_html($date_range_text); ?></p>
        <p>Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
    </div>

    <?php
    // Calculate totals
    $total_views = array_sum(array_column($data, 'total_views'));
    $total_clicks = array_sum(array_column($data, 'total_clicks'));
    $total_visitors = array_sum(array_column($data, 'unique_visitors'));
$avg_conversion = $total_visitors > 0 ? round(($total_clicks / $total_visitors) * 100, 2) : 0;
    ?>

    <div class="summary">
        <h3>Summary Statistics</h3>
        <div class="summary-grid">
            <div class="metric">
                <div class="metric-value"><?php echo number_format($total_views); ?></div>
                <div class="metric-label">Total Page Views</div>
            </div>
            <div class="metric">
                <div class="metric-value"><?php echo number_format($total_clicks); ?></div>
                <div class="metric-label">Total Button Clicks</div>
            </div>
            <div class="metric">
                <div class="metric-value"><?php echo $avg_conversion; ?>%</div>
                <div class="metric-label">Average Conversion Rate</div>
            </div>
        </div>
    </div>

    <h3>Page Performance Details</h3>
    <table>
        <thead>
            <tr>
                <th>Page Title</th>
                <th>Unique Visitors</th>
                <th>Total Views</th>
                <th>Button Clicks</th>
                <th>Conversion Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $page): 
                $conversion_rate = floatval($page['conversion_rate']);
                $rate_class = $conversion_rate >= 10 ? 'high-rate' : 
                            ($conversion_rate >= 5 ? 'medium-rate' : 'low-rate');
            ?>
            <tr>
                <td><?php echo esc_html($page['page_title']); ?></td>
                <td><?php echo number_format($page['unique_visitors']); ?></td>
                <td><?php echo number_format($page['total_views']); ?></td>
                <td><?php echo number_format($page['total_clicks']); ?></td>
                <td class="<?php echo $rate_class; ?>">
                    <?php echo number_format($conversion_rate, 1); ?>%
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated by Convly - WordPress Conversion Rate Tracker</p>
        <p>© <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?></p>
    </div>
</body>
</html>
        <?php
        exit;
    }

    /**
     * Format date range for display
     */
    private function format_date_range($date_range) {
        if (!$date_range) {
            return 'All Time';
        }

        $start = date('F j, Y', strtotime($date_range['start']));
        $end = date('F j, Y', strtotime($date_range['end']));

        if ($start === $end) {
            return $start;
        }

        return $start . ' - ' . $end;
    }
	
	/**
     * Generate single page PDF report
     */
    public function generate_single_page_report($data, $date_range) {
        // Set headers for download
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="convly-page-report-' . $data['page_info']->page_id . '-' . date('Y-m-d') . '.html"');

        $site_name = get_bloginfo('name');
        $date_range_text = $this->format_date_range($date_range);

        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Page Report - <?php echo esc_html($data['page_info']->page_title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #0073aa;
        }
        .header h1 {
            color: #0073aa;
            margin: 10px 0;
        }
        .summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        .metric {
            background: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .metric-value {
            font-size: 32px;
            font-weight: bold;
            color: #0073aa;
        }
        .metric-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .page-info {
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .page-info h3 {
            margin-top: 0;
            color: #333;
        }
        .page-info p {
            margin: 10px 0;
            color: #666;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Page Performance Report</h1>
        <h2><?php echo esc_html($data['page_info']->page_title); ?></h2>
        <p>Report Period: <?php echo esc_html($date_range_text); ?></p>
        <p>Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
    </div>

    <div class="summary">
        <h3>Performance Metrics</h3>
        <div class="summary-grid">
            <div class="metric">
                <div class="metric-value"><?php echo number_format($data['stats']->total_views); ?></div>
                <div class="metric-label">Total Page Views</div>
            </div>
            <div class="metric">
                <div class="metric-value"><?php echo number_format($data['stats']->unique_visitors); ?></div>
                <div class="metric-label">Unique Visitors</div>
            </div>
            <div class="metric">
                <div class="metric-value"><?php echo $data['stats']->conversion_rate; ?>%</div>
                <div class="metric-label">Conversion Rate</div>
            </div>
        </div>
    </div>

    <div class="page-info">
        <h3>Page Information</h3>
        <p><strong>Page URL:</strong> <a href="<?php echo esc_url($data['page_info']->page_url); ?>"><?php echo esc_html($data['page_info']->page_url); ?></a></p>
        <p><strong>Page Type:</strong> <?php echo esc_html(ucfirst($data['page_info']->page_type)); ?></p>
        <p><strong>Total Button Clicks:</strong> <?php echo number_format($data['stats']->total_clicks); ?></p>
        <p><strong>Status:</strong> <?php echo $data['page_info']->is_active ? 'Active' : 'Inactive'; ?></p>
    </div>

    <div class="footer">
        <p>This report was generated by Convly - WordPress Conversion Rate Tracker</p>
        <p>© <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?></p>
    </div>
</body>
</html>
        <?php
        exit;
    }
	
}