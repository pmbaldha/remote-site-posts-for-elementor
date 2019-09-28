<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_Remote_Site_Posts_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve oEmbed widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'remote-site-posts';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve oEmbed widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Remote Site Posts', 'remote-site-posts-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve oEmbed widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fa fa-clone';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'basic' );
	}

	/**
	 * Register oEmbed widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'remote-site-posts-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'url',
			array(
				'label'       => __( 'URL of WP Site', 'remote-site-posts-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'input_type'  => 'url',
				'placeholder' => __( 'https://wordpress.org', 'remote-site-posts-for-elementor' ),
			)
		);

		$this->add_control(
			'search',
			array(
				'label'       => __( 'Search', 'remote-site-posts-for-elementor' ),
				'description' => __( 'If you like to display all recent articles, Please leave it as empty', 'remote-site-posts-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'https://wptavern.com/',
				'placeholder' => __( 'Type the search word here', 'remote-site-posts-for-elementor' ),
			)
		);

		$this->add_control(
			'no',
			array(
				'label'       => __( 'No. of Posts', 'remote-site-posts-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'placeholder' => __( 'https://wordpress.org', 'remote-site-posts-for-elementor' ),
				'default'     => 5,
			)
		);

		$this->add_control(
			'title_heading',
			array(
				'label'   => __( 'Title tag', 'remote-site-posts-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'h5',
				'options' => [
					'h1' => __( 'H1', 'remote-site-posts-for-elementor' ),
					'h2' => __( 'H2', 'remote-site-posts-for-elementor' ),
					'h3' => __( 'H3', 'remote-site-posts-for-elementor' ),
					'h4' => __( 'H4', 'remote-site-posts-for-elementor' ),
					'h5' => __( 'H5', 'remote-site-posts-for-elementor' ),
					'h6' => __( 'H6', 'remote-site-posts-for-elementor' ),
				],
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render Remote site posts widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();
		$wp_url   = sanitize_text_field( $settings['url'] );
		if ( ! empty( $settings['url'] ) ) {
			$no = intval( $settings['no'] );
			if ( $no == 0 ) {
				$no = 5;
			}
			$url    = trailingslashit( $settings['url'] ) . 'wp-json/wp/v2/posts';
			$url    = add_query_arg( 'per_page', $no, $url );
			$search = sanitize_text_field( $settings['search'] );
			if ( ! empty( $search ) ) {
				$url = add_query_arg( 'search', $search, $url );
			}

			$response = wp_remote_get( $url );
			// Check for error
			if ( is_wp_error( $response ) ) {
				printf( esc_html__( "WP REST API for posts doesn't support for the %1s", 'remote-site-posts-for-elementor' ), $wp_url );
				// $data = wp_remote_retrieve_body( $response );
			}
			// return if not an error
			if ( ! is_wp_error( $response ) ) {

				// decode and return
				/*
				var_dump( );
				die;
				*/
				$response_status = wp_remote_retrieve_response_code( $response );

				if ( '200' == $response_status ) {
					$posts         = json_decode( wp_remote_retrieve_body( $response ) );
					$heading_tags  = array(
						'h1',
						'h2',
						'h3',
						'h4',
						'h5',
						'h6',
					);
					$title_heading = sanitize_text_field( $settings['title_heading'] );
					if ( ! in_array( $title_heading, $heading_tags ) ) {
						$title_heading = '<h5>';
					}

					foreach ( $posts as $post ) {
						do_action( 'remote_site_posts_for_elementor_before_post', $post );
						echo '<' . $title_heading . '>';
						echo '<a href="' . esc_url( $post->link ) . '" target="_blank">' . wp_kses_post( $post->title->rendered ) . '</a>';
						echo '</' . $title_heading . '>';
						do_action( 'remote_site_posts_for_elementor_after_post_title', $post );
						echo wp_kses_post( $post->excerpt->rendered );
						do_action( 'remote_site_posts_for_elementor_after_post', $post );
					}
				} else {
					printf( esc_html__( "WP REST API for posts doesn't support for the %1$s. Status code: %2$s", 'remote-site-posts-for-elementor' ), $wp_url, $response_status );
				}
			}
		}
	}

}
