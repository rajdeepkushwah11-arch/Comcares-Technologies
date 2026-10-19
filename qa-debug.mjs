export default async function run(page, ui) {
  const results = {};
  // Debug: what happens on submit?
  const resp = [];
  page.on("response", (r) => {
    if (r.url().includes("login.php"))
      resp.push(r.status() + " " + r.headers()["location"] || "");
  });
  await page.fill("#email", "admin@comcares.com");
  await page.fill("#password", "admin123");
  await page.click(".admin-login-card button");
  await page.waitForTimeout(6000);
  results.url = page.url();
  results.responses = resp;
  results.bodyStart = await page.evaluate(() =>
    document.body.innerText.slice(0, 120),
  );
  return results;
}
