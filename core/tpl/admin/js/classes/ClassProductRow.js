/*       ClassProductRow.js    */
export default class ProductRow {
    Quantity = 0;
    PurePrice = 0;
    ShippingPay = 0;
    Price = 0;
    OutPrice = 0;
    totalCost_WITHOUT_VAT = 0;
    totalVAT_Amount = 0;
    totalCost_WITH_VAT = 0;
    priority = 0;
    index = 0;
    CID = 1;
    currencyRate = 1;
    hasVATIncluded = 0;
    VAT_Rate = 20.0000;


    constructor( params ) {

        this.CID = params.CID;
        this.currencyRate = params.currencyRate;
        this.hasVATIncluded = params.hasVATIncluded;
        this.VAT_Rate = params.VAT_Rate;

        this.index = params.index;

        this.Quantity = params.Quantity;
        this.PurePrice = params.PurePrice;
        this.ShippingPay = params.ShippingPay;
        this.Price = params.Price;
        this.OutPrice = params.OutPrice;

        this.priority = params.priority;

        this.totalCost_WITHOUT_VAT = params.totalCost_WITHOUT_VAT;
        this.totalVAT_Amount = params.totalVAT_Amount;
        this.totalCost_WITH_VAT = params.totalCost_WITH_VAT;

    }



}