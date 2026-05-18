<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Security;
use app\models\Transaction;
use app\models\BudgetLimit;
use app\models\Goal;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ReportController extends Controller {
    private $transactionModel;
    private $budgetModel;
    private $goalModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->transactionModel = new Transaction($db);
        $this->budgetModel = new BudgetLimit($db);
        $this->goalModel = new Goal($db);
    }

    // Страница выбора отчёта
    public function index() {
        $this->requireAuth();
        $csrf_token = Security::generateCsrfToken();
        $this->render('reports/index', ['csrf_token' => $csrf_token]);
    }

    // Генерация и отображение отчёта (HTML)
    public function generate() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/reports');
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }

        $reportType = $_POST['report_type'] ?? 'transactions';
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-t');
        $month = $_POST['month'] ?? date('Y-m-01');

        $userId = $_SESSION['user_id'];
        $data = [];

        switch ($reportType) {
            case 'transactions':
                $data['summary'] = $this->transactionModel->getSummary($userId, $startDate, $endDate);
                $data['grouped'] = $this->transactionModel->getGroupedByCategory($userId, $startDate, $endDate);
                $data['start_date'] = $startDate;
                $data['end_date'] = $endDate;
                break;
            case 'budget':
                $data['budgetReport'] = $this->budgetModel->getBudgetReport($userId, $month);
                $data['month'] = $month;
                break;
            case 'goals':
                $data['goalsReport'] = $this->goalModel->getGoalsReport($userId);
                break;
        }

        $data['report_type'] = $reportType;
        $data['csrf_token'] = Security::generateCsrfToken();
        $this->render('reports/show', $data);
    }

    // Экспорт в Excel (XLSX)
    public function exportExcel() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $reportType = $_POST['report_type'] ?? 'transactions';
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-t');
        $month = $_POST['month'] ?? date('Y-m-01');
        $userId = $_SESSION['user_id'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        switch ($reportType) {
            case 'transactions':
                $this->fillTransactionsSheet($sheet, $userId, $startDate, $endDate);
                $filename = "transactions_{$startDate}_{$endDate}.xlsx";
                break;
            case 'budget':
                $this->fillBudgetSheet($sheet, $userId, $month);
                $filename = "budget_{$month}.xlsx";
                break;
            case 'goals':
                $this->fillGoalsSheet($sheet, $userId);
                $filename = "goals_report.xlsx";
                break;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // Экспорт в Word (DOCX)
    public function exportWord() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $reportType = $_POST['report_type'] ?? 'transactions';
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-t');
        $month = $_POST['month'] ?? date('Y-m-01');
        $userId = $_SESSION['user_id'];

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        switch ($reportType) {
            case 'transactions':
                $this->addTransactionsToWord($section, $userId, $startDate, $endDate);
                $filename = "transactions_{$startDate}_{$endDate}.docx";
                break;
            case 'budget':
                $this->addBudgetToWord($section, $userId, $month);
                $filename = "budget_{$month}.docx";
                break;
            case 'goals':
                $this->addGoalsToWord($section, $userId);
                $filename = "goals_report.docx";
                break;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }

    // ------------------------------------------------
    // Приватные методы для заполнения Excel
    // ------------------------------------------------
    private function fillTransactionsSheet($sheet, $userId, $start, $end) {
        $summary = $this->transactionModel->getSummary($userId, $start, $end);
        $grouped = $this->transactionModel->getGroupedByCategory($userId, $start, $end);

        $sheet->setCellValue('A1', 'Отчёт по доходам и расходам');
        $sheet->setCellValue('A2', "Период: $start – $end");
        $sheet->setCellValue('A3', 'Итого доходов:');
        $sheet->setCellValue('B3', $summary['total_income']);
        $sheet->setCellValue('A4', 'Итого расходов:');
        $sheet->setCellValue('B4', $summary['total_expense']);
        $sheet->setCellValue('A5', 'Баланс:');
        $sheet->setCellValue('B5', $summary['balance']);

        $row = 7;
        $sheet->setCellValue("A$row", 'Доходы по категориям');
        $row++;
        $sheet->setCellValue("A$row", 'Категория');
        $sheet->setCellValue("B$row", 'Сумма');
        $row++;
        foreach ($grouped['income'] as $cat) {
            $sheet->setCellValue("A$row", $cat['name']);
            $sheet->setCellValue("B$row", $cat['total']);
            $row++;
        }

        $row += 2;
        $sheet->setCellValue("A$row", 'Расходы по категориям');
        $row++;
        $sheet->setCellValue("A$row", 'Категория');
        $sheet->setCellValue("B$row", 'Сумма');
        $row++;
        foreach ($grouped['expense'] as $cat) {
            $sheet->setCellValue("A$row", $cat['name']);
            $sheet->setCellValue("B$row", $cat['total']);
            $row++;
        }
        foreach (range('A', 'B') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    private function fillBudgetSheet($sheet, $userId, $month) {
        $report = $this->budgetModel->getBudgetReport($userId, $month);
        $sheet->setCellValue('A1', 'Отчёт по бюджету');
        $sheet->setCellValue('A2', "Месяц: $month");
        $sheet->setCellValue('A4', 'Категория');
        $sheet->setCellValue('B4', 'Лимит');
        $sheet->setCellValue('C4', 'Потрачено');
        $sheet->setCellValue('D4', 'Остаток');
        $sheet->setCellValue('E4', '%');
        $row = 5;
        foreach ($report as $item) {
            $sheet->setCellValue("A$row", $item['name']);
            $sheet->setCellValue("B$row", $item['limit_amount']);
            $sheet->setCellValue("C$row", $item['current_spent']);
            $sheet->setCellValue("D$row", $item['remaining']);
            $sheet->setCellValue("E$row", $item['percent'] . '%');
            $row++;
        }
        foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    private function fillGoalsSheet($sheet, $userId) {
        $goals = $this->goalModel->getGoalsReport($userId);
        $sheet->setCellValue('A1', 'Отчёт по финансовым целям');
        $sheet->setCellValue('A3', 'Название цели');
        $sheet->setCellValue('B3', 'Нужно');
        $sheet->setCellValue('C3', 'Накоплено');
        $sheet->setCellValue('D3', 'Процент');
        $sheet->setCellValue('E3', 'Статус');
        $row = 4;
        foreach ($goals as $goal) {
            $sheet->setCellValue("A$row", $goal['name']);
            $sheet->setCellValue("B$row", $goal['target_amount']);
            $sheet->setCellValue("C$row", $goal['current_amount']);
            $sheet->setCellValue("D$row", $goal['percent'] . '%');
            $sheet->setCellValue("E$row", $goal['is_completed'] ? 'Выполнена' : 'В процессе');
            $row++;
        }
        foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ------------------------------------------------
    // Приватные методы для Word
    // ------------------------------------------------
    private function addTransactionsToWord($section, $userId, $start, $end) {
        $summary = $this->transactionModel->getSummary($userId, $start, $end);
        $grouped = $this->transactionModel->getGroupedByCategory($userId, $start, $end);

        $section->addTitle('Отчёт по доходам и расходам', 1);
        $section->addText("Период: $start – $end");
        $section->addText("Итого доходов: {$summary['total_income']}");
        $section->addText("Итого расходов: {$summary['total_expense']}");
        $section->addText("Баланс: {$summary['balance']}");

        $section->addTitle('Доходы по категориям', 2);
        $table = $section->addTable();
        $table->addRow()->addCell()->addText('Категория');
        $table->addRow()->addCell()->addText('Сумма');
        foreach ($grouped['income'] as $cat) {
            $row = $table->addRow();
            $row->addCell()->addText($cat['name']);
            $row->addCell()->addText($cat['total']);
        }

        $section->addTitle('Расходы по категориям', 2);
        $table = $section->addTable();
        $table->addRow()->addCell()->addText('Категория');
        $table->addRow()->addCell()->addText('Сумма');
        foreach ($grouped['expense'] as $cat) {
            $row = $table->addRow();
            $row->addCell()->addText($cat['name']);
            $row->addCell()->addText($cat['total']);
        }
    }

    private function addBudgetToWord($section, $userId, $month) {
        $report = $this->budgetModel->getBudgetReport($userId, $month);
        $section->addTitle('Отчёт по бюджету', 1);
        $section->addText("Месяц: $month");
        $table = $section->addTable();
        $header = $table->addRow();
        $header->addCell()->addText('Категория');
        $header->addCell()->addText('Лимит');
        $header->addCell()->addText('Потрачено');
        $header->addCell()->addText('Остаток');
        $header->addCell()->addText('%');
        foreach ($report as $item) {
            $row = $table->addRow();
            $row->addCell()->addText($item['name']);
            $row->addCell()->addText($item['limit_amount']);
            $row->addCell()->addText($item['current_spent']);
            $row->addCell()->addText($item['remaining']);
            $row->addCell()->addText($item['percent'] . '%');
        }
    }

    private function addGoalsToWord($section, $userId) {
        $goals = $this->goalModel->getGoalsReport($userId);
        $section->addTitle('Отчёт по финансовым целям', 1);
        $table = $section->addTable();
        $header = $table->addRow();
        $header->addCell()->addText('Название');
        $header->addCell()->addText('Нужно');
        $header->addCell()->addText('Накоплено');
        $header->addCell()->addText('Процент');
        $header->addCell()->addText('Статус');
        foreach ($goals as $goal) {
            $row = $table->addRow();
            $row->addCell()->addText($goal['name']);
            $row->addCell()->addText($goal['target_amount']);
            $row->addCell()->addText($goal['current_amount']);
            $row->addCell()->addText($goal['percent'] . '%');
            $row->addCell()->addText($goal['is_completed'] ? 'Выполнена' : 'В процессе');
        }
    }
}