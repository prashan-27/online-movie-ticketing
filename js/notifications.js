document.addEventListener("DOMContentLoaded", function () {
  console.log("JavaScript is working!");

  function showNotification(message, type) {
      console.log(`Notification triggered: ${message} - ${type}`);

      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.textContent = message;

      // Create a close button
      const closeBtn = document.createElement('span');
      closeBtn.innerHTML = '&times;';
      closeBtn.className = 'close-btn';
      closeBtn.onclick = () => notification.remove();

      notification.appendChild(closeBtn);

      // Position notifications dynamically
      const notifications = document.querySelectorAll('.notification');
      const offset = 20 + (notifications.length * 60); // Adjust spacing
      notification.style.right = '20px';
      notification.style.top = `${offset}px`;

      document.body.appendChild(notification);

      // Trigger animation
      requestAnimationFrame(() => notification.classList.add('visible'));

      // Auto-remove after 3 seconds
      setTimeout(() => {
          notification.classList.remove('visible');
          setTimeout(() => notification.remove(), 50);
      }, 30);
  }
});
