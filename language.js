var language = {

    strings: {},

    init(languageFilePath) {
        // Load language file
        $.get(languageFilePath, {}, function(r) {
            // Iterate over lines
            var lines = r.split('\n');
            for(var i = 0; i < lines.length; i++) {
                // If line contains '=', split there and add string to collection
                var j = lines[i].indexOf('=');
                if (j != -1)
                    language.strings[lines[i].substr(0, j)] = lines[i].substr(j + 1);
            }

            language.updateAgo();
        }, 'text');
    },

    updateAgo() {
        $('[time-ago]').each(function(i, e) {
            $e = $(e);
            $e.html(language.formatAgo($e.attr('time-unix')));
        });
        window.setTimeout(function() {
            language.updateAgo();
        }, 1000);
    },

    curUnix() {
        return Math.floor(+new Date() / 1000);
    },

    formatAgo(timestamp, type) {
        if (!timestamp)
            return this.strings['datetime.never'];

        if (!type)
            type = 'full';

        var dif = this.curUnix() - Math.floor(timestamp);
        if (dif < 0) {
            format = 'datetime.dif.syntax_future';
            dif = -dif;
        } else if (dif < 10) {
            return this.strings['datetime.dif.just_now'];
        } else {
            format = 'datetime.dif.syntax_past';
        }

        var greatNames = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year'];
        var greatLengths = [1, 60, 3600, 86400, 604800, 2635200, 31557600];

        for(var greatIndex = 0; greatIndex <= 6; greatIndex++) {
            if (dif <= greatLengths[greatIndex]) {
                if (greatIndex > 4)
                    return this.formatTime(timestamp, type);
                else {
                    var amount = Math.floor(dif / (greatLengths[greatIndex - 1]));
                    var index = 'datetime.dif.' + greatNames[greatIndex - 1] + (amount == 1 ? '' : 's');
                    return this.strings[format].replace('%v', amount).replace('%q', this.strings[index]);
                }
            }
        }
    },

    formatTime(timestamp, type) {
        if (!type)
            type = 'full';
        var syntax = this.strings['datetime.syntax.full'];
        if (typeof this.strings['datetime.syntax.' + type] !== 'undefined')
            syntax = this.strings['datetime.syntax.' + type];

        var pad = function(s) { return ('0' + s).substr(-2); }

        var d = new Date(timestamp * 1000);
        return syntax
        .replace('%n', this.strings['datetime.day.' + (1 + d.getDay())])
        .replace('%d', d.getDate())
        .replace('%m', this.strings['datetime.month.' + (1 + d.getMonth())])
        .replace('%y', d.getFullYear())
        .replace('%H', pad(d.getHours()))
        .replace('%t', pad(d.getMinutes()))
        .replace('%s', pad(d.getSeconds()))
        .replace('%h', d.getHours() > 12 ? pad(d.getHours()) - 12 : pad(d.getHours()))
        .replace('%A', this.strings[d.getHours() > 12 ? 'datetime.pm' : 'datetime.am']);
    },

    translate(key) {
        if (typeof this.strings[key] == 'undefined')
            return key;

        var replacement = this.escapeHtml(this.strings[key]);

        for(var i = 1; i < arguments.length; i++)
            replacement = replacement.replace('%' + i, arguments[i]);

        return replacement;
    },
  
    escapeHtml(string) {
        if (string == null)
            return null;
        
        rA = function(s, a, b) {
            while(s.indexOf(a) != -1) {
                s = s.replace(a, b);
            }
            return s;
        };
        string = string.replace(/\&/g, '&amp;').replace(/\"/g, '&quot;');
        string = rA(string, '<', '&lt;');
        string = rA(string, '>', '&gt;');
        return string;
    },
    

}
