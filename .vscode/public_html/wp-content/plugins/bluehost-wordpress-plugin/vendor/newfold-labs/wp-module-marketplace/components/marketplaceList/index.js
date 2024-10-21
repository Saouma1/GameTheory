
import { default as MarketplaceItem } from '../marketplaceItem/';
import { Button, Title } from "@newfold/ui-component-library";

/**
 * MarketplaceList Component
 * For use in Marketplace to display a list of marketplace items
 * 
 * @param {*} props 
 * @returns 
 */
const MarketplaceList = ({ marketplaceItems, currentCount, category, methods, constants }) => {
	const [ itemsCount, setItemsCount ] = methods.useState( currentCount );
	const [ currentItems, setCurrentItems ] = methods.useState( [] );
	const [ activeItems, setActiveItems ] = methods.useState( [] )

	/**
	 * Filter Products By Category - this ensures only this category products is listed here, it gets us current items
	 * @param Array items - the products
	 * @param string category - the category to filter by 
	 * @returns 
	 */
	const filterProductsByCategory = (items, category) => {
		return items.filter((item) => {
			return item.categories.includes( category.title );
		});
	};

	/**
	 * Set Product List Length - this controls how many products are displayed in the list, it gets us active current items
	 * @param Array items 
	 * @param Number itemsCount 
	 * @returns 
	 */
	const setProductListCount = (items, itemsCount) => {
		let count = 0;
		return items.filter((item) => {
			count++;
			return count <= itemsCount;
		});
	};

	/**
	 * increment itemCount by perPage amount
	 */
	const loadMoreClick = () => {
		setItemsCount( itemsCount + constants.perPage );
	};

	/**
	 * init method - filter products
	 */
	methods.useEffect(() => {
		setCurrentItems( filterProductsByCategory(marketplaceItems, category) );
	}, [ marketplaceItems ]);

	/**
	 * recalculate activeItems if currentItems or itemsCount changes
	 */
	methods.useEffect(() => {
		setActiveItems( setProductListCount(currentItems, itemsCount) );
	}, [ currentItems, itemsCount ] );

	return (
		<>
			<div className={ `marketplace-list marketplace-list-${ category.name } wppbh-app-marketplace-list nfd-grid nfd-gap-6 nfd-grid-cols-1 min-[1120px]:nfd-grid-cols-2 min-[1400px]:nfd-grid-cols-3` }>
				{ activeItems.length > 0 && activeItems.map((item) => (
					<MarketplaceItem
						key={item.id} 
						item={item}
						methods={methods}
						constants={constants}
					/>
					))
				}
				{ !activeItems.length &&
					<p>{ constants.text.noProducts }</p>
				}
				{ currentItems && currentItems.length > itemsCount &&
					<div style={{ display: 'flex', margin: '1rem 0'}}>
						<Button
							onClick={loadMoreClick}
							variant="primary" 
							className="align-center"
							style={{margin: 'auto'}}
							>
							{constants.text.loadMore}
						</Button>
					</div>
				}
			</div>
		</>
	)
};

export default MarketplaceList;