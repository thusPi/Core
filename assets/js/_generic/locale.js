thusPiAssign('locale', {
    translate: function(key, replacements = []) {
        if(!(key in thusPi.data.locale.translations)) {
            return key;
        }

        let translation = thusPi.data.locale.translations[key]
        let replacements_count = replacements.length;

        for (let i = replacements_count-1; i >= 0; i--) {
            if(i < replacements_count) {
                translation = translation.replace(`%${i}`, replacements[i]);
                continue;
            }
            break;
        }

        return translation;
    },

    formatDate: function(date, timeFormat = 'short', dateFormat = 'short') {
        if(typeof date == 'number') {
            // Convert unix timestamp to date
            if(date < 100000000000) {
                // Unix timestamp is in seconds
                date = new Date(date * 1000);
            } else {
                // Unix timestamp is in milliseconds
                date = new Date(date);
            }
        } else if(typeof date == 'string') {
            // Convert ISO to date object
            date = new Date(date);
        } else {
            return false;
        }

        const relativeDayName = 'AAA';
        const amPmTranslation = 'AA';
        const weekDayName = 'AAAA';

        return thusPi.locale.translate(`generic.time_format.date_${dateFormat || timeFormat}_time_${dateFormat}`, [
            padLeft(date.getHours()),    // 0: Time in 24-hour format
            padLeft(date.getMinutes()),  // 1: Minutes of hour
            padLeft(date.getSeconds()),  // 2: Seconds of hour
            relativeDayName,             // 3: Name of day (relative)
            padLeft(date.getDate()),     // 4: Day of month
            padLeft(date.getMonth()+1),  // 5: Month of year
            date.getFullYear(),          // 6: Year
            date.getHours() % 12 || 12,  // 7: Time in 12-hour format
            amPmTranslation,             // 8: AM or PM,
            weekDayName                  // 9: Name of day of week
        ]);

        // $.each(formatSplit, function(i, char) {
        //     switch(char) {
        //         case 'H': // Hour of day
        //             output += padLeft(date.getHours());
        //             break;
        //         case 'i': // Minute of hour
        //             output += padLeft(date.getMinutes());
        //             break;
        //         case 'd': // Day of month
        //             output += date.getDate();
        //             break;
        //         case 'l': // Day of month (translated)
        //             output += thuspi.locale.translate(`generic.day.${date.getDay()}`);
        //             break;
        //         case 'm': // Month of year
        //             output += padLeft(date.getMonth() + 1);
        //             break;
        //         case 'F': // Month of year (translated)
        //             output += thuspi.locale.translate(`generic.month.${date.getMonth()}`);
        //             break;
        //         case 'Y': // Year
        //             output += date.getFullYear();
        //             break;
        //         default:
        //             output += char;
        //             break;
        //     }
        // })

        return output;  
    }
})
