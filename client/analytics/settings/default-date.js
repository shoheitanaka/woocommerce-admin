/** @format */
/**
 * External dependencies
 */
import { parse, stringify } from 'qs';
/**
 * WooCommerce dependencies
 */
import { DateRangeFilterPicker } from '@woocommerce/components';

/**
 * Internal dependencies
 */
import { analyticsSettings } from './config';

const DefaultDate = ( { value, onChange } ) => {
	const change = query => {
		const queryString = stringify( query );
		// todo: move this to onChange so that changes persist. Others using object equality
		// Update window settings so navigating away from settings without a refresh reflects changes.
		wcSettings.wcAdminSettings.woocommerce_default_date_range = queryString;
		const initialConfig = analyticsSettings.find(
			setting => 'woocommerce_default_date_range' === setting.name
		);
		initialConfig.initialValue = queryString;

		onChange( {
			target: {
				name: 'woocommerce_default_date_range',
				value: queryString,
			},
		} );
	};
	const query = parse( value.replace( /&amp;/g, '&' ) );
	return <DateRangeFilterPicker query={ query } onRangeSelect={ change } />;
};

export default DefaultDate;
