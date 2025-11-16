import { useForm } from '@inertiajs/react';
import { FormField } from '@/components/FormField';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';

interface Company {
  id?: number;
  name: string;
  email: string;
  phone: string;
  website: string;
  industry: string;
  employee_count: number;
  address: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  notes: string;
}

interface Props {
  company?: Company;
}

export default function CompaniesForm({ company }: Props) {
  const isEditing = !!company?.id;
  const { data, setData, post, put, processing, errors } = useForm(
    company || {
      name: '',
      email: '',
      phone: '',
      website: '',
      industry: '',
      employee_count: '',
      address: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      notes: '',
    }
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (isEditing) {
      put(`/companies/${company.id}`);
    } else {
      post('/companies');
    }
  };

  return (
    <AppLayout title={isEditing ? `Edit ${company.name}` : 'Create Company'}>
      <PageHeader
        title={isEditing ? `Edit ${company.name}` : 'Create Company'}
        description={
          isEditing
            ? 'Update company information'
            : 'Add a new company to your CRM'
        }
      />

      <Card className="max-w-2xl">
        <CardHeader>
          <CardTitle>
            {isEditing ? 'Edit Company' : 'New Company'}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Basic Information */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Basic Information</h3>

              <FormField
                label="Company Name"
                error={errors.name}
                required
              >
                <input
                  type="text"
                  value={data.name}
                  onChange={(e) => setData('name', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="Acme Corporation"
                />
              </FormField>

              <FormField
                label="Email"
                error={errors.email}
              >
                <input
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="contact@company.com"
                />
              </FormField>

              <FormField
                label="Phone"
                error={errors.phone}
              >
                <input
                  type="tel"
                  value={data.phone}
                  onChange={(e) => setData('phone', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="+1 (555) 123-4567"
                />
              </FormField>

              <FormField
                label="Website"
                error={errors.website}
              >
                <input
                  type="url"
                  value={data.website}
                  onChange={(e) => setData('website', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="https://company.com"
                />
              </FormField>
            </div>

            {/* Business Details */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Business Details</h3>

              <FormField
                label="Industry"
                error={errors.industry}
              >
                <input
                  type="text"
                  value={data.industry}
                  onChange={(e) => setData('industry', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="Technology, Finance, Retail, etc."
                />
              </FormField>

              <FormField
                label="Employee Count"
                error={errors.employee_count}
              >
                <input
                  type="number"
                  min="1"
                  value={data.employee_count}
                  onChange={(e) => setData('employee_count', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="1000"
                />
              </FormField>
            </div>

            {/* Address */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Address</h3>

              <FormField
                label="Street Address"
                error={errors.address}
              >
                <input
                  type="text"
                  value={data.address}
                  onChange={(e) => setData('address', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="123 Main Street"
                />
              </FormField>

              <div className="grid grid-cols-2 gap-4">
                <FormField
                  label="City"
                  error={errors.city}
                >
                  <input
                    type="text"
                    value={data.city}
                    onChange={(e) => setData('city', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="New York"
                  />
                </FormField>

                <FormField
                  label="State/Province"
                  error={errors.state}
                >
                  <input
                    type="text"
                    value={data.state}
                    onChange={(e) => setData('state', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="NY"
                  />
                </FormField>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <FormField
                  label="Postal Code"
                  error={errors.postal_code}
                >
                  <input
                    type="text"
                    value={data.postal_code}
                    onChange={(e) => setData('postal_code', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="10001"
                  />
                </FormField>

                <FormField
                  label="Country"
                  error={errors.country}
                >
                  <input
                    type="text"
                    value={data.country}
                    onChange={(e) => setData('country', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="United States"
                  />
                </FormField>
              </div>
            </div>

            {/* Additional Information */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Additional Information</h3>

              <FormField
                label="Notes"
                error={errors.notes}
              >
                <textarea
                  value={data.notes}
                  onChange={(e) => setData('notes', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="Internal notes about this company..."
                  rows={4}
                />
              </FormField>
            </div>

            {/* Submit Buttons */}
            <div className="flex gap-2 justify-end pt-4">
              <Button
                type="button"
                variant="outline"
                onClick={() => window.history.back()}
                disabled={processing}
              >
                Cancel
              </Button>
              <Button
                type="submit"
                disabled={processing}
              >
                {isEditing ? 'Update Company' : 'Create Company'}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </AppLayout>
  );
}





