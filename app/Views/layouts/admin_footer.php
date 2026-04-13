            </div>
        </main>
    </div><!-- /WRAPPER PRINCIPAL -->

    <!-- Footer visible -->
    <footer class="hidden">
        <!-- El footer de la biblioteca pública no aplica aquí -->
    </footer>

    <!-- JS: notificaciones (presente en todas las páginas admin) -->
    <script src="<?= BASE_URL ?>/js/admin/notificaciones.js"></script>

    <!-- JS: toggle del sidebar en móvil -->
    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }
        function closeSidebar() {
            document.getElementById('adminSidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.add('hidden');
        }
    </script>

</body>
</html>
