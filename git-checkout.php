<?php

/**
 * Plugin Name: Git Checkout
 * Description: 開発用にテーマのブランチを切り替えたり、最新のコミットを反映したりします。
 * Author:      Yuto Ono
 * Text Domain: git-checkout
 * Domain Path: /languages
 * Version:     0.1.0
 */
defined('ABSPATH') || exit;

if (!defined('GIT_CHECKOUT_DIR')) {
  define('GIT_CHECKOUT_DIR', ABSPATH . 'wp-content/themes/my-theme');
}

class GitCheckout
{
  /**
   * @var static
   */
  static protected $instance;

  protected function __construct()
  {
    add_action('admin_menu', [$this, 'adminMenu']);
  }

  static public function instance()
  {
    return self::$instance ?? (self::$instance = new static);
  }

  public function adminMenu()
  {
    add_menu_page(
      'Git Checkout',
      'Git Checkout',
      'manage_options',
      'git-checkout',
      [$this, 'render'],
      '',
      '4'
    );
  }

  public function render()
  {
    chdir(GIT_CHECKOUT_DIR);
    if ($_POST['branch']) {
      $branch = escapeshellarg($_POST['branch']);
      `git reset --hard && git fetch && git checkout $branch && git merge`;
    }
    $currentBranch = trim(`git symbolic-ref --short HEAD`);
    $latestCommit = `git log -1`;
    $branches = `git branch -r`;
    $branches = explode("\n", $branches);
    $branches = array_map(function ($branch) {
      return str_replace('  origin/', '', $branch);
    }, $branches);
    $branches = array_filter($branches, function ($branch) {
      return !empty($branch) && strpos($branch, 'HEAD') !== 0;
    });
?>
    <form action="" method="post" class="wrap">
      <h1>Select Branch</h1>
      <?php foreach ($branches as $branch) { ?>
        <p style="font-size: 1.25em;">
          <label>
            <input type="radio" name="branch" value="<?= $branch ?>" <?= $branch === $currentBranch ? 'checked' : '' ?>>
            <?= $branch; ?>
          </label>
        </p>
      <?php } ?>
      <p class="submit">
        <input type="submit" class="button button-primary" value="Checkout">
      </p>
      <h2>Latest Commit</h2>
      <pre><?= $latestCommit ?></pre>
    </form>
<?php
  }
}

GitCheckout::instance();
