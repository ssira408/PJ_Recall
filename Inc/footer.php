</div> <!-- ปิด .container -->

<!-- Footer -->
<footer style="
    background: var(--main-color);
    color: var(--header-text);
    text-align:center;
    padding:5px 0;
    margin-top:32px;
    box-shadow:0 -2px 6px rgba(0,0,0,0.1);
    font-size:16px;

">
    <p>&copy; <?= date('Y') ?> Project Recall</p>
</footer>

<!-- Scroll Top Button -->
<script>
// สร้างปุ่ม Scroll Top
const scrollBtn = document.createElement('button');
scrollBtn.textContent = "⬆ Top";
scrollBtn.style.cssText = `position: fixed; bottom: 20px; right: 20px; padding: 10px 14px; border-radius: 8px; border: none; background: var(--main-color); color: #fff; cursor: pointer; display: none; z-index: 999; box-shadow: 0 2px 6px rgba(0,0,0,0.2); transition: all 0.3s ease;`;
document.body.appendChild(scrollBtn);

// Scroll smooth to top
scrollBtn.addEventListener('click', () => {
    window.scrollTo({top:0, behavior:'smooth'});
});

// แสดง/ซ่อนปุ่มตาม scroll
window.addEventListener('scroll', () => {
    if (window.scrollY > 200) {
        scrollBtn.style.display = 'block';
        scrollBtn.style.opacity = '1';
    } else {
        scrollBtn.style.opacity = '0';
        setTimeout(() => scrollBtn.style.display = 'none', 300); // ให้ fade out
    }
});
</script>

<!-- CSS Variables -->
<style>
:root {
    --main-color: #b40000; /* สีแดงเข้ม */
    --header-text: #ffffff; /* สีตัวอักษร */
}
</style>

</body>
</html>
