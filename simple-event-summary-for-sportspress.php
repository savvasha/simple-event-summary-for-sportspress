<?php
/**
 * Plugin Name: Simple Event Summary for SportsPress
 * Description: Add a brief event summary (i.e. scorers) below Event main card.
 * Version: 1.0
 * Author: Savvas
 * Author URI: https://savvasha.com
 * Requires at least: 5.3
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl.html
 *
 * @package simple-event-summary-for-sportspress
 * @category Core
 * @author savvasha
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
if ( ! defined( 'ESFS_PLUGIN_BASE' ) ) {
	define( 'ESFS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'ESFS_PLUGIN_DIR' ) ) {
	define( 'ESFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'ESFS_PLUGIN_URL' ) ) {
	define( 'ESFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Hooks.
add_filter( 'sportspress_event_settings', 'esfs_add_settings' );

add_action( 'sportspress_after_event_logos', 'esfs_event_summary' );
add_action( 'wp_enqueue_scripts', 'esfs_adding_scripts' );

/**
 * Add settings for the Event Summary.
 *
 * @param array $settings Existing SportsPress event settings.
 * @return array Modified event settings.
 */
function esfs_add_settings( $settings ) {
	// Initialize arrays to store performance, team, and official data.
	$esfs_performances = array();
	$esfs_teams        = array();
	$esfs_officials    = array();

	// Retrieve and populate performance data.
	$sp_performances = get_posts(
		array(
			'post_type'   => 'sp_performance',
			'numberposts' => -1,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		)
	);
	foreach ( $sp_performances as $sp_performance ) {
		$esfs_performances[ $sp_performance->post_name ] = $sp_performance->post_title;
	}

	// Retrieve and populate team data.
	$sp_teams = get_posts(
		array(
			'post_type'   => 'sp_team',
			'numberposts' => -1,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		)
	);
	foreach ( $sp_teams as $sp_team ) {
		$esfs_teams[ $sp_team->ID ] = $sp_team->post_title;
	}

	// Retrieve and populate officials data.
	$sp_officials = get_terms(
		array(
			'taxonomy'   => 'sp_duty',
			'hide_empty' => false,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		)
	);
	foreach ( $sp_officials as $sp_official ) {
		$esfs_officials[ $sp_official->slug ] = $sp_official->name;
	}

	// Merge SportsPress Event Settings with additional Event Summary options.
	$settings = array_merge(
		$settings,
		array(
			array(
				'title' => __( 'Event Summary', 'sportspress' ),
				'type'  => 'title',
				'id'    => 'esfs_event_summary_options',
			),
		),
		apply_filters(
			'esfs_event_summary_options',
			array(
				array(
					'title'         => __( 'Display', 'sportspress' ),
					'desc'          => __( 'Performances', 'sportspress' ),
					'id'            => 'esfs_show_performances',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
				),

				array(
					'desc'          => __( 'Officials', 'sportspress' ),
					'id'            => 'esfs_show_officials',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => 'end',
				),
				array(
					'title'   => esc_attr__( 'Scoring Performances to show (i.e. goals, penalties etc.)', 'sportspress' ),
					'id'      => 'esfs_show_scoring_performances_list',
					'type'    => 'multiselect',
					'options' => $esfs_performances,
				),
				array(
					'title'   => esc_attr__( 'Special Scoring Performances to show (i.e. own goals)', 'sportspress' ),
					'id'      => 'esfs_show_special_scoring_performances_list',
					'type'    => 'multiselect',
					'options' => $esfs_performances,
				),
				array(
					'title'   => esc_attr__( 'Other Performances to show (i.e. assists)', 'sportspress' ),
					'id'      => 'esfs_show_other_performances_list',
					'type'    => 'multiselect',
					'options' => $esfs_performances,
				),
				array(
					'title'   => esc_attr__( 'Officials to show', 'sportspress' ),
					'id'      => 'esfs_show_officials_list',
					'type'    => 'multiselect',
					'options' => $esfs_officials,
				),
				array(
					'title'   => esc_attr__( 'Display summary on events with teams (empty for all)', 'sportspress' ),
					'id'      => 'esfs_show_teams_list',
					'type'    => 'multiselect',
					'options' => $esfs_teams,
				),
			)
		),
		array(
			array(
				'type' => 'sectionend',
				'id'   => 'esfs_event_summary_options',
			),
		)
	);
	return $settings;
}

/**
 * Display event summary including scorers and referee information.
 *
 * @param int $id Event ID.
 */
function esfs_event_summary( $id ) {
	// Set the ID if not provided.
	if ( ! isset( $id ) ) {
		$id = get_the_ID();
	}

	$teams_filtering      = get_option( 'esfs_show_teams_list', false );
	$event_teams_findings = true;

	if ( $teams_filtering && ! empty( $teams_filtering ) ) {
		$event_teams          = get_post_meta( $id, 'sp_team', false );
		$teams_filtering      = array_map( 'intval', $teams_filtering );
		$event_teams_findings = array_intersect( $teams_filtering, $event_teams );
	}

	if ( true === $event_teams_findings || ( is_array( $event_teams_findings ) && ! empty( $event_teams_findings ) ) ) {

		// Create a new SP_Event instance.
		$event = new SP_Event( $id );

		// Output HTML for summary.
		echo '<div class="match-header-resume">';
		echo '<table>';
		echo '<tbody>';

		if ( 'yes' === get_option( 'esfs_show_performances', 'yes' ) ) {
			// Get linear timeline from event.
			$timeline = $event->timeline( false, true );
			// Get players link option.
			$link_players = get_option( 'sportspress_link_players', 'no' ) === 'yes' ? true : false;
			// Gather all selected performances in one array.
			$scoring_performances         = get_option( 'esfs_show_scoring_performances_list', array() );
			$special_scoring_performances = get_option( 'esfs_show_special_scoring_performances_list', array() );
			$other_performances           = get_option( 'esfs_show_other_performances_list', array() );
			$esfs_all_performances        = array_merge( $scoring_performances, $special_scoring_performances, $other_performances );

			// Initiate variables.
			$summary_array = array();
			$goals_home    = 0;
			$goals_away    = 0;

			// Iterate through the event timeline.
			foreach ( $timeline as $details ) {
				$time = sp_array_value( $details, 'time', false );
				if ( false === $time || $time < 0 ) {
					continue;
				}

				$key = sp_array_value( $details, 'key', '' );
				if ( in_array( $key, $esfs_all_performances, true ) ) {
					$side = sp_array_value( $details, 'side', 'home' );

					if ( 'home' === $side ) {
						if ( in_array( $key, $scoring_performances, true ) ) {
							$goals_home++;
							$details['goals_home'] = $goals_home;
							$details['goals_away'] = $goals_away;
							$summary_array[]       = $details;
						} elseif ( in_array( $key, $special_scoring_performances, true ) ) {
							$goals_away++;
							$details['side']       = 'away';
							$details['goals_home'] = $goals_home;
							$details['goals_away'] = $goals_away;
							$summary_array[]       = $details;
						} else {
							$details['goals_home'] = null;
							$details['goals_away'] = null;
							$summary_array[]       = $details;
						}
					} elseif ( 'away' === $side ) {
						if ( in_array( $key, $scoring_performances, true ) ) {
							$goals_away++;
							$details['goals_home'] = $goals_home;
							$details['goals_away'] = $goals_away;
							$summary_array[]       = $details;
						} elseif ( in_array( $key, $special_scoring_performances, true ) ) {
							$goals_home++;
							$details['side']       = 'home';
							$details['goals_home'] = $goals_home;
							$details['goals_away'] = $goals_away;
							$summary_array[]       = $details;
						} else {
							$details['goals_home'] = null;
							$details['goals_away'] = null;
							$summary_array[]       = $details;
						}
					}
				}
			}

			// Iterate through summary details and display information.
			foreach ( $summary_array as $summary_row ) {
				$side       = sp_array_value( $summary_row, 'side', 'home' );
				$icon       = sp_array_value( $summary_row, 'icon', '' );
				$time       = sp_array_value( $summary_row, 'time', false );
				$home_goals = sp_array_value( $summary_row, 'goals_home', false );
				$away_goals = sp_array_value( $summary_row, 'goals_away', false );
				$delimiter  = $home_goals || $away_goals ? '-' : '';

				// Generate player name with or without link based on settings.
				if ( $link_players ) {
					$name = '<a href="' . esc_url( get_permalink( sp_array_value( $summary_row, 'id', '' ) ) ) . '">' . sp_array_value( $summary_row, 'name', __( 'Player', 'sportspress' ) ) . '</a>';
				} else {
					$name = sp_array_value( $summary_row, 'name', __( 'Player', 'sportspress' ) );
				}

				// Display performances summary in table rows.
				echo '<tr>';
				if ( 'home' === $side ) {
					echo '<td class="mhr-name">' . wp_kses_post( $name ) . ' ' . wp_kses_post( $time ) . '\' ' . wp_kses_post( $icon ) . '</td>';
					echo '<td class="mhr-marker"><div>' . wp_kses_post( $home_goals ) . esc_attr( $delimiter ) . wp_kses_post( $away_goals ) . '</div></td>';
					echo '<td class="mhr-name"></td>';
				} else {
					echo '<td class="mhr-name"></td>';
					echo '<td class="mhr-marker"><div>' . wp_kses_post( $home_goals ) . esc_attr( $delimiter ) . wp_kses_post( $away_goals ) . '</div></td>';
					echo '<td class="mhr-name">' . wp_kses_post( $icon ) . ' ' . wp_kses_post( $time ) . '\' ' . wp_kses_post( $name ) . '</td>';
				}
				echo '</tr>';
			}
		}

		// Display officials information.
		if ( 'yes' === get_option( 'esfs_show_officials', 'yes' ) ) {
			// Get appointed officials from event.
			$data                = $event->appointments();
			$link_officials      = get_option( 'sportspress_link_officials', 'no' ) === 'yes' ? true : false;
			$esfs_show_officials = get_option( 'esfs_show_officials_list', array() );

			// The first row should be column labels.
			if ( isset( $data[0] ) ) {
				$labels = $data[0];
				unset( $data[0] );
			}

			// Display officials information.
			foreach ( $esfs_show_officials as $esfs_show_official ) {
				if ( isset( $data[ $esfs_show_official ] ) ) {
					foreach ( $data[ $esfs_show_official ] as $official_id => $official_name ) {
						if ( $link_officials && sp_post_exists( $official_id ) ) {
							$official_name = '<a href="' . get_post_permalink( $official_id ) . '">' . $official_name . '</a>';
						}

						// Display referee information in table rows.
						echo '<tr><td colspan="100%" class="mhr-referee-name">' . wp_kses_post( $labels[ $esfs_show_official ] . ': ' . $official_name ) . '</td></tr>';
					}
				}
			}
		}

		echo '</tbody>';
		echo '</table>';
		echo '</div>';
	}
}

/**
 * Load scripts and styles where needed
 *
 * @return void
 */
function esfs_adding_scripts() {
	global $post;
	if ( is_singular( 'sp_event' ) ) {
		// Some css code.
		wp_enqueue_style( 'simple_event_summary_for_sportspress', ESFS_PLUGIN_URL . 'assets/css/front.css', array(), '1.0.0' );

	}

}
