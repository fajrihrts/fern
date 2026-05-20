<?php
/**
 * Simple Pagination Class
 * Works with any database query
 */

class Paginator {
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    private $offset;
    
    public function __construct($totalItems, $itemsPerPage = 10, $currentPage = 1) {
        $this->totalItems = (int) $totalItems;
        $this->itemsPerPage = (int) $itemsPerPage;
        $this->currentPage = max(1, (int) $currentPage);
        $this->totalPages = max(1, ceil($this->totalItems / $this->itemsPerPage));
        
        // Ensure current page doesn't exceed total pages
        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }
        
        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    /**
     * Get SQL LIMIT clause
     */
    public function getLimit() {
        return "LIMIT {$this->itemsPerPage} OFFSET {$this->offset}";
    }
    
    /**
     * Get offset for query
     */
    public function getOffset() {
        return $this->offset;
    }
    
    /**
     * Get items per page
     */
    public function getPerPage() {
        return $this->itemsPerPage;
    }
    
    /**
     * Get current page
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Get total pages
     */
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    /**
     * Get total items
     */
    public function getTotal() {
        return $this->totalItems;
    }
    
    /**
     * Check if there's a previous page
     */
    public function hasPrevious() {
        return $this->currentPage > 1;
    }
    
    /**
     * Check if there's a next page
     */
    public function hasNext() {
        return $this->currentPage < $this->totalPages;
    }
    
    /**
     * Get previous page number
     */
    public function getPreviousPage() {
        return max(1, $this->currentPage - 1);
    }
    
    /**
     * Get next page number
     */
    public function getNextPage() {
        return min($this->totalPages, $this->currentPage + 1);
    }
    
    /**
     * Get page numbers to display
     */
    public function getPages($maxLinks = 5) {
        $pages = [];
        
        if ($this->totalPages <= $maxLinks) {
            // Show all pages
            for ($i = 1; $i <= $this->totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            // Show limited pages with current page in center
            $half = floor($maxLinks / 2);
            $start = max(1, $this->currentPage - $half);
            $end = min($this->totalPages, $start + $maxLinks - 1);
            
            // Adjust start if end is at max
            if ($end == $this->totalPages) {
                $start = max(1, $end - $maxLinks + 1);
            }
            
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
        }
        
        return $pages;
    }
    
    /**
     * Get info text (e.g., "Showing 1-10 of 50")
     */
    public function getInfo() {
        if ($this->totalItems == 0) {
            return 'Tidak ada data';
        }
        
        $start = $this->offset + 1;
        $end = min($this->offset + $this->itemsPerPage, $this->totalItems);
        
        return "Menampilkan {$start}-{$end} dari {$this->totalItems}";
    }
    
    /**
     * Render pagination HTML
     */
    public function render($baseUrl = '', $queryParams = [], $useAjax = false) {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $html = '<div class="nb-pagination">';
        
        // Previous button
        if ($this->hasPrevious()) {
            $prevPage = $this->getPreviousPage();
            if ($useAjax) {
                $html .= '<a href="javascript:void(0)" onclick="loadLogs(' . $prevPage . ')" class="nb-pagination-link">';
            } else {
                $url = $this->buildUrl($baseUrl, $prevPage, $queryParams);
                $html .= '<a href="' . $url . '" class="nb-pagination-link">';
            }
            $html .= '<i class="bi bi-chevron-left"></i> Sebelumnya';
            $html .= '</a>';
        } else {
            $html .= '<span class="nb-pagination-link disabled">';
            $html .= '<i class="bi bi-chevron-left"></i> Sebelumnya';
            $html .= '</span>';
        }
        
        // Page numbers
        $pages = $this->getPages();
        
        // First page if not in range
        if ($pages[0] > 1) {
            if ($useAjax) {
                $html .= '<a href="javascript:void(0)" onclick="loadLogs(1)" class="nb-pagination-number">1</a>';
            } else {
                $url = $this->buildUrl($baseUrl, 1, $queryParams);
                $html .= '<a href="' . $url . '" class="nb-pagination-number">1</a>';
            }
            if ($pages[0] > 2) {
                $html .= '<span class="nb-pagination-dots">...</span>';
            }
        }
        
        // Page numbers
        foreach ($pages as $page) {
            $class = $page == $this->currentPage ? 'nb-pagination-number active' : 'nb-pagination-number';
            if ($useAjax) {
                $html .= '<a href="javascript:void(0)" onclick="loadLogs(' . $page . ')" class="' . $class . '">' . $page . '</a>';
            } else {
                $url = $this->buildUrl($baseUrl, $page, $queryParams);
                $html .= '<a href="' . $url . '" class="' . $class . '">' . $page . '</a>';
            }
        }
        
        // Last page if not in range
        if ($pages[count($pages) - 1] < $this->totalPages) {
            if ($pages[count($pages) - 1] < $this->totalPages - 1) {
                $html .= '<span class="nb-pagination-dots">...</span>';
            }
            if ($useAjax) {
                $html .= '<a href="javascript:void(0)" onclick="loadLogs(' . $this->totalPages . ')" class="nb-pagination-number">' . $this->totalPages . '</a>';
            } else {
                $url = $this->buildUrl($baseUrl, $this->totalPages, $queryParams);
                $html .= '<a href="' . $url . '" class="nb-pagination-number">' . $this->totalPages . '</a>';
            }
        }
        
        // Next button
        if ($this->hasNext()) {
            $nextPage = $this->getNextPage();
            if ($useAjax) {
                $html .= '<a href="javascript:void(0)" onclick="loadLogs(' . $nextPage . ')" class="nb-pagination-link">';
            } else {
                $url = $this->buildUrl($baseUrl, $nextPage, $queryParams);
                $html .= '<a href="' . $url . '" class="nb-pagination-link">';
            }
            $html .= 'Selanjutnya <i class="bi bi-chevron-right"></i>';
            $html .= '</a>';
        } else {
            $html .= '<span class="nb-pagination-link disabled">';
            $html .= 'Selanjutnya <i class="bi bi-chevron-right"></i>';
            $html .= '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Build URL with page parameter
     */
    private function buildUrl($baseUrl, $page, $queryParams = []) {
        $queryParams['page'] = $page;
        $query = http_build_query($queryParams);
        
        return $baseUrl . ($query ? '?' . $query : '');
    }
    
    /**
     * Static helper to create paginator from query
     */
    public static function fromQuery($db, $countQuery, $itemsPerPage = 10) {
        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $totalItems = $db->query($countQuery)->fetchColumn();
        
        return new self($totalItems, $itemsPerPage, $currentPage);
    }
}
