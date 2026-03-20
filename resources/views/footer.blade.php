<footer style="background:#8d99b6; color:#fff; text-align:center; padding:20px; font-size:14px;" class="footer">
  &copy; <span id="year"></span> <strong>CAAW</strong>. All rights reserved. <br>
  Built with &hearts; for better productivity.
</footer>

<style>
/* Dark mode styles for footer */
.dark .footer {
  background: #1e293b;
  color: #e2e8f0;
}
</style>

<script>
  document.getElementById("year").textContent = new Date().getFullYear();
</script>