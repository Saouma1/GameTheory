import { default as MarketplaceList } from '../marketplaceList/';
import { default as MarketplaceIsLoading } from '../marketplaceIsLoading/';
import { Title } from "@newfold/ui-component-library";

const defaults = {
	'eventendpoint': '/newfold-data/v1/events/',
	'perPage': 12,
	'supportsCTB': true,
	'appendCategoryToTitle': true,
	'text': {
		'title':      'Marketplace',
		'subTitle':   'Explore our featured collection of tools and services.',
		'error':      'Oops, there was an error loading the marketplace, please try again later.',
		'noProducts': 'Sorry, no marketplace items. Please, try again later.',
		'loadMore':   'Load More',
	},
};

/**
 * Marketplace Module
 * For use in brand app to display marketplace
 * 
 * @param {*} props 
 * @returns 
 */
 const Marketplace = ({methods, constants, Components, ...props}) => {
	const [ isLoading, setIsLoading ] = methods.useState( true );
	const [ isError, setIsError ] = methods.useState( false );
	const [ marketplaceCategories, setMarketplaceCategories ] = methods.useState( [] );
	const [ marketplaceItems, setMarketplaceItems ] = methods.useState( [] );
    const [ products, setProducts ] = methods.useState( [] );
    const [ activeCategoryIndex, setActiveCategoryIndex ] = methods.useState( 0 );
	let location = methods.useLocation();

	// set defaults if not provided
	constants = Object.assign(defaults, constants);

	/**
	 * on mount load all marketplace data from module api
	 */
	methods.useEffect(() => {
		methods.apiFetch( {
			url: methods.NewfoldRuntime.createApiUrl( '/newfold-marketplace/v1/marketplace' )
		}).then( ( response ) => {
			// check response for data
			if ( ! response.hasOwnProperty('categories') || ! response.hasOwnProperty('products') ) {
				setIsError( true );
			} else {
				setMarketplaceItems( response.products.data );
				setMarketplaceCategories( validateCategories(response.categories.data) );
			}
		});
	}, [] );

	/**
	 * When marketplaceItems changes
	 * verify that there are products
	 */
	 methods.useEffect(() => {
		// only after a response
		if ( !isLoading ) {
			// if no marketplace items, display error
			if ( marketplaceItems.length < 1 ) {
				setIsError( true );
			} else {
				setIsError( false );
			}
		}
	}, [ marketplaceItems, products ] );

	/**
	 * When marketplaceCategories changes
	 * verify that the tab is a category
	 */
	 methods.useEffect(() => {
		let aci = 0;
		// only before rendered, but after categories are populated
		if ( marketplaceCategories.length > 1 ) {
			// read initial tab from path
			if ( location.pathname.includes( 'marketplace/' ) ) {
				const urlpath = location.pathname.substring( 
					location.pathname.lastIndexOf( '/' ) + 1
				);

				// make sure a category exists for that path
				if ( urlpath && marketplaceCategories.filter(cat => cat.name === urlpath ).length != 0 ) {
					// if found, set the active category
					marketplaceCategories.forEach((cat, i) => {
						if ( cat.name === urlpath ) {
							aci = i;
						}
					});
				}
			}
			setActiveCategoryIndex( aci );
			filterProducts( aci );
			applyStyles();
		}
	}, [ marketplaceCategories, location.pathname ] );

	/**
	 * Filter products based on urlpath
	 */
	const filterProducts = ( activeCategoryIndex ) => {
		const category = marketplaceCategories[activeCategoryIndex].name;
        const filterdProducts = marketplaceItems.filter((product) => {
            return product.categories.some(element => {
                return element.toLowerCase() === category.toLowerCase();
              });
              
        });            

        setProducts(filterdProducts);
        setIsLoading(false);
    };

	/**
	 * Validate provided category data
	 * @param Array categories 
	 * @returns 
	 */
	const validateCategories = ( categories ) => {
		
		if ( ! categories.length ) {
			return [];
		}
		
		let thecategories = [];
		categories.forEach((cat)=>{
			cat.currentCount = constants.perPage;
			cat.className = 'newfold-marketplace-category-'+cat.name;

			if ( cat.products_count > 0 ) {
				thecategories.push(cat);
			}
		});
		
		return thecategories;
	};

	/**
	 * Apply styles if they exist
	 */
	 const applyStyles = () => {
		if ( marketplaceCategories ) {
			marketplaceCategories.forEach( (category) => {
				if( 
					category.styles && // category has styles
					!document.querySelector('[data-styleid="' + category.className + '"]') // not already added
				) {
					const style = document.createElement("style")
					style.textContent = category.styles;
					style.dataset.styleid = category.className;
					document.head.appendChild(style);
				}
			});
		}
	};

	/**
	 * render marketplace preloader
	 * 
	 * @returns React Component
	 */
	 const renderSkeleton = () => {
		// render default skeleton
		return <MarketplaceIsLoading />;
	};

	const getSectionTitle = () => {
		if ( !isLoading && !isError && marketplaceCategories && constants.appendCategoryToTitle ) {
			return constants.text.title + ': ' + marketplaceCategories[activeCategoryIndex].title;
		}
		else {
			return constants.text.title;
		}
	};

	return (
		<>
			<Components.SectionHeader
				title={getSectionTitle()}
				subTitle={constants.text.subTitle}
			/>
			<Components.SectionContent className={methods.classnames(
				'newfold-marketplace-wrapper',
				`newfold-marketplace-${marketplaceCategories[activeCategoryIndex]}`
				)}>
				{ isLoading && 
					renderSkeleton()
				}
				{ isError && 
					<Title as="h3" size="3">
						{ constants.text.error }
					</Title>
				}
				{ !isLoading && !isError &&
						<MarketplaceList
							marketplaceItems={products}
							category={marketplaceCategories[activeCategoryIndex]}
							currentCount={marketplaceCategories[activeCategoryIndex].currentCount}
							methods={methods}
							constants={constants}
						/>

				}
			</Components.SectionContent>
		</>
	)

};

export default Marketplace;