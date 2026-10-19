export default async function run(page, ui) {
  const results = {};

  // Login page loads with new theme
  results.loginCardAnim = await page.evaluate(() => {
    const c = document.querySelector('.admin-login-card');
    return c && getComputedStyle(c).animationName !== 'none';
  });
  results.badgePresent = await page.locator('.admin-badge').count();

  // Wrong password shows error
  await page.fill('#email', 'admin@comcares.com');
  await page.fill('#password', 'wrongpass');
  await page.click('.admin-login-card button');
  await page.waitForSelector('.admin-error', { timeout: 15000 });
  results.wrongPwError = true;

  // Correct login → dashboard
  await page.fill('#password', 'admin123');
  await page.click('.admin-login-card button');
  await page.waitForURL(/dashboard\.php/, { timeout: 60000 }).catch(() => {});
  results.onDashboard = page.url().includes('dashboard.php');
  if (!results.onDashboard) return results;

  results.topbarSticky = await page.evaluate(() => {
    const t = document.querySelector('.admin-topbar');
    return t && getComputedStyle(t).position === 'sticky';
  });
  results.statCards = await page.locator('.stat-card').count();
  results.headingAnim = await page.evaluate(() => {
    const h = document.querySelector('.admin-heading');
    return h && getComputedStyle(h).animationName !== 'none';
  });
  results.msgCards = await page.locator('.msg-card').count();
  results.todayStat = await page.evaluate(() => document.body.innerText.includes('Received today'));

  if (results.msgCards > 0) {
    const before = await page.locator('.msg-card').first().evaluate(el => getComputedStyle(el).transform);
    await page.locator('.msg-card').first().hover();
    await page.waitForTimeout(400);
    const after = await page.locator('.msg-card').first().evaluate(el => getComputedStyle(el).transform);
    results.hoverLift = before !== after;
  }

  // Logout works
  await page.click('.admin-logout');
  await page.waitForURL(/login\.php/, { timeout: 60000 }).catch(() => {});
  results.logoutRedirects = page.url().includes('login.php');

  return results;
}
