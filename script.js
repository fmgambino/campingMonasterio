(() => {
  const html = document.documentElement;
  const themeToggle = document.getElementById("themeToggle");

  const reserveModal = document.getElementById("reserveModal");
  const includesModal = document.getElementById("includesModal");
  const videoModal = document.getElementById("videoModal");

  const reserveButtons = document.querySelectorAll(".reserve-btn");
  const includeButtons = document.querySelectorAll(".include-btn");
  const reserveForm = document.getElementById("reserveForm");
  const formResponse = document.getElementById("formResponse");

  const packageId = document.getElementById("package_id");
  const packageTitle = document.getElementById("package_title");
  const packageDays = document.getElementById("package_days");
  const packageNights = document.getElementById("package_nights");
  const pricingMode = document.getElementById("pricing_mode");
  const packagePrice = document.getElementById("package_price");
  const adultPrice = document.getElementById("adult_price");
  const youthPrice = document.getElementById("youth_price");

  const guests = document.getElementById("guests");
  const adults = document.getElementById("adults");
  const youth = document.getElementById("youth");
  const adultsGroup = document.getElementById("adultsGroup");
  const youthGroup = document.getElementById("youthGroup");

  const checkinInput = document.getElementById("checkin");
  const checkoutInput = document.getElementById("checkout");
  const paymentType = document.getElementById("payment_type");

  const summaryPackage = document.getElementById("summaryPackage");
  const summaryDuration = document.getElementById("summaryDuration");
  const summaryTotal = document.getElementById("summaryTotal");
  const summaryDue = document.getElementById("summaryDue");
  const modalTitle = document.getElementById("modalTitle");
  const modalSubtitle = document.getElementById("modalSubtitle");

  const includesTitle = document.getElementById("includesTitle");
  const includesList = document.getElementById("includesList");

  const videoFrameWrap = document.getElementById("videoFrameWrap");
  const videoTitle = document.getElementById("videoTitle");

  const sliders = document.querySelectorAll(".slider-arrow");
  const videoOpenButtons = document.querySelectorAll(".media-open");
  const autoplaySliders = document.querySelectorAll(".autoplay-slider");

  function formatCurrency(amount) {
    return new Intl.NumberFormat("es-AR", {
      style: "currency",
      currency: "ARS",
      maximumFractionDigits: 0
    }).format(Number(amount || 0));
  }

  function setTheme(theme) {
    html.setAttribute("data-theme", theme);
    localStorage.setItem("camping-theme", theme);
  }

  function initTheme() {
    const saved = localStorage.getItem("camping-theme");
    if (saved === "light" || saved === "dark") {
      setTheme(saved);
      return;
    }
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    setTheme(prefersDark ? "dark" : "light");
  }

  function openModal(modal) {
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeModal(modal) {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");

    if (
      !reserveModal.classList.contains("is-open") &&
      !includesModal.classList.contains("is-open") &&
      !videoModal.classList.contains("is-open")
    ) {
      document.body.style.overflow = "";
    }
  }

  function addDays(dateStr, days) {
    const date = new Date(`${dateStr}T11:00:00`);
    date.setDate(date.getDate() + Number(days));

    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");

    return `${yyyy}-${mm}-${dd}`;
  }

  function updateCheckout() {
    const checkin = checkinInput.value;
    const days = Number(packageDays.value || 0);

    if (!checkin || !days) {
      checkoutInput.value = "";
      return;
    }

    checkoutInput.value = addDays(checkin, days);
  }

  function togglePerPersonFields() {
    const isPerPerson = pricingMode.value === "per_person";
    adultsGroup.style.display = isPerPerson ? "flex" : "none";
    youthGroup.style.display = isPerPerson ? "flex" : "none";

    adults.required = isPerPerson;
    youth.required = isPerPerson;
  }

  function syncGuestsTotal() {
    if (pricingMode.value !== "per_person") return;

    const totalAdults = Number(adults.value || 0);
    const totalYouth = Number(youth.value || 0);
    guests.value = totalAdults + totalYouth;
  }

  function calculateTotal() {
    if (pricingMode.value === "per_person") {
      const totalAdults = Number(adults.value || 0);
      const totalYouth = Number(youth.value || 0);
      const aPrice = Number(adultPrice.value || 0);
      const yPrice = Number(youthPrice.value || 0);
      return totalAdults * aPrice + totalYouth * yPrice;
    }

    return Number(packagePrice.value || 0);
  }

  function updateSummary() {
    const title = packageTitle.value || "-";
    const days = Number(packageDays.value || 0);
    const nights = Number(packageNights.value || 0);
    const total = calculateTotal();
    const due = paymentType.value === "deposit_50" ? total * 0.5 : total;

    summaryPackage.textContent = title;
    summaryDuration.textContent = nights > 0
      ? `${days} días / ${nights} noches`
      : `${days} día`;

    summaryTotal.textContent = formatCurrency(total);
    summaryDue.textContent = formatCurrency(due);

    modalTitle.textContent = `Reservar ${title}`;
    modalSubtitle.textContent = `Estadía de ${summaryDuration.textContent}. Check-in 11:00 hs | Check-out 10:00 hs.`;
  }

  function resetFormResponse() {
    formResponse.className = "form-response";
    formResponse.textContent = "";
  }

  function setFormResponse(type, message) {
    formResponse.className = `form-response is-${type}`;
    formResponse.innerHTML = message;
  }

  function setMinDate() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const dd = String(today.getDate()).padStart(2, "0");
    checkinInput.min = `${yyyy}-${mm}-${dd}`;
  }

  function populatePackage(button) {
    packageId.value = button.dataset.packageId;
    packageTitle.value = button.dataset.packageTitle;
    packageDays.value = button.dataset.packageDays;
    packageNights.value = button.dataset.packageNights;
    pricingMode.value = button.dataset.pricingMode;

    packagePrice.value = button.dataset.packagePrice || "";
    adultPrice.value = button.dataset.adultPrice || "";
    youthPrice.value = button.dataset.youthPrice || "";

    guests.value = 1;
    adults.value = 0;
    youth.value = 0;

    if (pricingMode.value === "per_person") {
      adults.value = 1;
      youth.value = 0;
      guests.value = 1;
    }

    togglePerPersonFields();
    updateCheckout();
    updateSummary();
  }

  function makeTikTokEmbed(url) {
    const match = url.match(/tiktok\.com\/@[^/]+\/video\/(\d+)/i);

    if (match) {
      const videoId = match[1];
      return `
        <iframe
          src="https://www.tiktok.com/embed/v2/${videoId}?autoplay=1"
          allow="autoplay; encrypted-media"
          allowfullscreen
          title="TikTok video player"
        ></iframe>
      `;
    }

    return `
      <iframe
        src="${url}"
        allow="autoplay; encrypted-media"
        allowfullscreen
        title="Video"
      ></iframe>
    `;
  }

  function initAutoplaySlider(track) {
    let interval = null;

    const start = () => {
      stop();

      interval = setInterval(() => {
        const card = track.querySelector(":scope > *");
        if (!card) return;

        const styles = getComputedStyle(track);
        const gap = parseInt(styles.columnGap || styles.gap || 18, 10);
        const step = card.getBoundingClientRect().width + gap;

        track.scrollLeft += step;

        if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 5) {
          track.scrollLeft = 0;
        }
      }, 3000);
    };

    const stop = () => {
      if (interval) clearInterval(interval);
    };

    track.addEventListener("mouseenter", stop);
    track.addEventListener("mouseleave", start);
    track.addEventListener("touchstart", stop, { passive: true });
    track.addEventListener("touchend", start, { passive: true });

    start();
  }

  themeToggle?.addEventListener("click", () => {
    const current = html.getAttribute("data-theme");
    setTheme(current === "dark" ? "light" : "dark");
  });

  reserveButtons.forEach((button) => {
    button.addEventListener("click", () => {
      reserveForm.reset();
      resetFormResponse();
      setMinDate();
      populatePackage(button);
      openModal(reserveModal);
    });
  });

  includeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      includesTitle.textContent = `Incluye ${button.dataset.includeTitle}`;
      includesList.innerHTML = "";

      const items = JSON.parse(button.dataset.includeList || "[]");
      items.forEach((item) => {
        const li = document.createElement("li");
        li.textContent = item;
        includesList.appendChild(li);
      });

      openModal(includesModal);
    });
  });

  checkinInput?.addEventListener("change", updateCheckout);
  paymentType?.addEventListener("change", updateSummary);

  adults?.addEventListener("input", () => {
    syncGuestsTotal();
    updateSummary();
  });

  youth?.addEventListener("input", () => {
    syncGuestsTotal();
    updateSummary();
  });

  guests?.addEventListener("input", () => {
    if (pricingMode.value !== "fixed") return;
    updateSummary();
  });

  document.querySelectorAll("[data-close-modal]").forEach((element) => {
    element.addEventListener("click", () => closeModal(reserveModal));
  });

  document.querySelectorAll("[data-close-includes]").forEach((element) => {
    element.addEventListener("click", () => closeModal(includesModal));
  });

  document.querySelectorAll("[data-close-video]").forEach((element) => {
    element.addEventListener("click", () => {
      videoFrameWrap.innerHTML = "";
      closeModal(videoModal);
    });
  });

  sliders.forEach((button) => {
    button.addEventListener("click", () => {
      const target = document.getElementById(button.dataset.target);
      if (!target) return;

      const firstCard = target.querySelector(":scope > *");
      const styles = getComputedStyle(target);
      const gap = parseInt(styles.columnGap || styles.gap || 18, 10);
      const step = firstCard ? firstCard.getBoundingClientRect().width + gap : 380;

      target.scrollBy({
        left: button.classList.contains("next") ? step : -step,
        behavior: "smooth"
      });
    });
  });

  videoOpenButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const url = button.dataset.videoUrl;
      const title = button.dataset.videoTitle || "Video";

      videoTitle.textContent = title;
      videoFrameWrap.innerHTML = makeTikTokEmbed(url);
      openModal(videoModal);
    });
  });

  reserveForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    resetFormResponse();

    if (pricingMode.value === "per_person") {
      const totalGuests = Number(guests.value || 0);
      if (totalGuests <= 0) {
        setFormResponse("error", "Ingresá al menos una persona para continuar.");
        return;
      }
    }

    const endpoint = window.APP_CONFIG?.reserveEndpoint || "reserve.php";
    const formData = new FormData(reserveForm);
    formData.set("calculated_total", String(calculateTotal()));

    const submitBtn = document.getElementById("submitReservation");
    submitBtn.disabled = true;
    submitBtn.textContent = "Procesando reserva...";

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        body: formData
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(data.message || "No se pudo procesar la reserva.");
      }

      let message = `<strong>Reserva enviada correctamente.</strong><br>${data.message || ""}`;

      if (data.payment_redirect) {
        message += `<br><br><a class="text-link" href="${data.payment_redirect}" target="_blank" rel="noopener">Ir al pago</a>`;
      }

      if (data.whatsapp_url) {
        message += `<br><br><a class="text-link" href="${data.whatsapp_url}" target="_blank" rel="noopener">Enviar por WhatsApp</a>`;
      }

      setFormResponse("success", message);
      reserveForm.reset();
      checkoutInput.value = "";
      togglePerPersonFields();
      updateSummary();
    } catch (error) {
      setFormResponse("error", error.message || "Ocurrió un error inesperado.");
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Confirmar reserva";
    }
  });

  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      if (reserveModal.classList.contains("is-open")) closeModal(reserveModal);
      if (includesModal.classList.contains("is-open")) closeModal(includesModal);
      if (videoModal.classList.contains("is-open")) {
        videoFrameWrap.innerHTML = "";
        closeModal(videoModal);
      }
    }
  });

  autoplaySliders.forEach(initAutoplaySlider);

  initTheme();
  setMinDate();
  togglePerPersonFields();
})();