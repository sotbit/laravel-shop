
const addressData = require('china-area-data/v3/data');

import _ from 'lodash';


Vue.component('select-district', {
    
    props: {
        
        initValue: {
            type: Array, 
            default: () => ([]), 
        }
    },
    
    data() {
        return {
            provinces: addressData['86'], 
            cities: {}, 
            districts: {}, 
            provinceId: '', 
            cityId: '', 
            districtId: '', 
        };
    },
    
    watch: {
        
        provinceId(newVal) {
            if (!newVal) {
                this.cities = {};
                this.cityId = '';
                return;
            }
            
            this.cities = addressData[newVal];
            
            if (!this.cities[this.cityId]) {
                this.cityId = '';
            }
        },
        
        cityId(newVal) {
            if (!newVal) {
                this.districts = {};
                this.districtId = '';
                return;
            }
            
            this.districts = addressData[newVal];
            
            if (!this.districts[this.districtId]) {
                this.districtId = '';
            }
        },
        
        districtId() {
            
            this.$emit('change', [this.provinces[this.provinceId], this.cities[this.cityId], this.districts[this.districtId]]);
        },
    },
    
    created() {
        this.setFromValue(this.initValue);
    },
    methods: {
        
        setFromValue(value) {
            
            value = _.filter(value);
            
            if (value.length === 0) {
                this.provinceId = '';
                return;
            }
            
            const provinceId = _.findKey(this.provinces, o => o === value[0]);
            
            if (!provinceId) {
                this.provinceId = '';
                return;
            }
            
            this.provinceId = provinceId;
            
            
            const cityId = _.findKey(addressData[provinceId], o => o === value[1]);
            
            if (!cityId) {
                this.cityId = '';
                return;
            }
            
            this.cityId = cityId;
            
            
            const districtId = _.findKey(addressData[cityId], o => o === value[2]);
            
            if (!districtId) {
                this.districtId = '';
                return;
            }
            
            this.districtId = districtId;
        }
    }
});