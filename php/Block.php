<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			array(
				'render_callback' => array( $this, 'render_callback' ),
			)
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {
		$post_types = get_post_types( [ 'public' => true ] );
		$class_name = $attributes['className'];
		ob_start();
		?>

		<div class="<?php echo esc_attr( $class_name ); ?>">
			<h2><?php esc_html_e( 'Post Counts', 'site-counts' ); ?></h2>
			<ul>
			<?php
			foreach ( $post_types as $post_type_slug ) :
				$post_type_object = get_post_type_object( $post_type_slug );
				$post_count       = count(
					get_posts(
						[
							'post_type'      => $post_type_slug,
							'posts_per_page' => -1, // Can we maybe limit this for performance?
						]
					)
				);
				?>
				<li>
					<?php
					echo sprintf(
						'%1$s %2$d %3$s',
						esc_html__( 'There are', 'site-counts' ),
						absint( $post_count ),
						esc_html( $post_type_object->labels->name )
					);
					?>
				</li>
			<?php endforeach; ?>
			</ul>
			<p>
			<?php
				echo sprintf(
					'%1$s %2$d.',
					esc_html__( 'The current post ID is', 'site-counts' ),
					absint( get_the_ID() )
				);
			?>
			</p>
			<?php
			$current_post = get_the_ID();
			$query        = new WP_Query(
				[
					'post_type'     => [ 'post', 'page' ],
					'post_status'   => 'any',
					'date_query'    => [
						[
							'hour'    => 9,
							'compare' => '>=',
						],
						[
							'hour'    => 17,
							'compare' => '<=',
						],
					],
					'tag'           => 'foo',
					'category_name' => 'baz',
				]
			);
			?>
			<?php if ( $query->found_posts ) : ?>
				<h2><?php esc_html_e( 'Any 5 posts with the tag of foo and the category of baz', 'site-counts' ); ?></h2>
				<ul>
					<?php foreach ( array_slice( $query->posts, 0, 5 ) as $post ) : ?>
						<?php if ( $post->ID !== $current_post ) : ?>
							<li><?php echo esc_html( $post->post_title ); ?></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}
}
