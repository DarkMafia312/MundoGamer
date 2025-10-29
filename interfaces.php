<?php

interface Connectable {
    public function connect();
    public function getConnection();
}

interface Exportable {
    public function exportToExcel($data, $filename);
}
?>