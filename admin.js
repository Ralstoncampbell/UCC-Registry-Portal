/*
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A Javascript file to contain JS functions that work with admin_dashboard.php and student_dashboard.php. Allows JS to run with no security issues as opposed to placing it in the actual .php file.>

*/

function showTab(tabName) {
    console.log("you just click me" ,tabName);
    document.querySelectorAll('.tab-section').forEach(section => {
        section.classList.remove('active');
    });

    document.getElementById(tabName).classList.add('active');

    localStorage.setItem('activeTab', tabName);
}

document.addEventListener("DOMContentLoaded", function () {
    const activeTab = localStorage.getItem('activeTab') || 'students';
    showTab(activeTab);
});