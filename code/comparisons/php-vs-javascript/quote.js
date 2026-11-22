const form = document.querySelector("#quote-form");
const quantity = document.querySelector("#quantity");
const button = form.querySelector("button");
const result = document.querySelector("#quote-result");

if ([window.fetch, window.FormData, window.URLSearchParams, window.AbortController]
    .every((api) => typeof api === "function")) {
  quantity.addEventListener("input", () => {
    quantity.setAttribute("aria-invalid", "false");
    result.textContent = "";
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (button.disabled) return;

    const body = new URLSearchParams(new FormData(form));
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 5000);
    button.disabled = true;
    quantity.readOnly = true;
    result.textContent = "Calculating quote…";

    try {
      const response = await fetch(form.action, {
        method: "POST",
        headers: { Accept: "application/json" },
        signal: controller.signal,
        body,
      });
      const contentType = response.headers.get("content-type")?.split(";")[0].trim();
      if (contentType !== "application/json" || (!response.ok && response.status !== 422)) {
        throw new Error("Unexpected quote response");
      }

      const { message } = await response.json();
      if (typeof message !== "string" || message.trim() === "") {
        throw new Error("Missing quote message");
      }

      quantity.setAttribute("aria-invalid", String(response.status === 422));
      result.textContent = message;
    } catch {
      result.textContent = controller.signal.aborted
        ? "The quote request timed out. Try again."
        : "The quote could not be calculated. Please try again.";
    } finally {
      clearTimeout(timeout);
      quantity.readOnly = false;
      button.disabled = false;
    }
  });
}
